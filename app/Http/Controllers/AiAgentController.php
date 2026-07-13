<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Services\AgentService;
use App\Services\AiToolsService;
use Illuminate\Support\Facades\Auth;

class AiAgentController extends Controller
{
    public function index()
    {
        return view('ai-agent.chat');
    }

    public function getSessions()
    {
        $sessions = ChatSession::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json($sessions);
    }

    public function loadSession($id)
    {
        $session = ChatSession::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $messages = $session->messages()->orderBy('created_at', 'asc')->get();
        return response()->json(['session' => $session, 'messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|exists:chat_sessions,id'
        ]);

        $user = Auth::user();

        // Get or create session
        if ($request->session_id) {
            $session = ChatSession::where('id', $request->session_id)->where('user_id', $user->id)->firstOrFail();
            $session->touch(); // Update updated_at
        } else {
            $title = substr($request->message, 0, 30) . (strlen($request->message) > 30 ? '...' : '');
            $session = ChatSession::create([
                'user_id' => $user->id,
                'title' => $title
            ]);
        }

        // Save user message
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $request->message
        ]);

        // Build description of available tools for the prompt
        $tools = AiToolsService::getToolsDefinition($user->role);
        $toolsDescription = "";
        foreach ($tools as $t) {
            $func = $t['function'];
            $toolsDescription .= "- Name: " . $func['name'] . "\n";
            $toolsDescription .= "  Description: " . $func['description'] . "\n";
            if (isset($func['parameters']['properties'])) {
                $toolsDescription .= "  Parameters:\n";
                foreach ($func['parameters']['properties'] as $paramName => $paramDetails) {
                    $type = $paramDetails['type'] ?? 'string';
                    $desc = $paramDetails['description'] ?? '';
                    $toolsDescription .= "    * {$paramName} ({$type}): {$desc}\n";
                }
            }
            $toolsDescription .= "\n";
        }

        $roleName = $user->role === 'admin' ? 'Admin Manager' : 'Employee';
        $systemPrompt = "You are an AI {$roleName} Assistant. You have access to database tools.
You MUST respond in valid JSON format matching one of these two schemas.

If you need to call a tool to answer the user's question, return:
{
  \"action\": \"tool_call\",
  \"tool_name\": \"name_of_the_tool\",
  \"arguments\": { \"arg_name\": \"value\" }
}

If you have all the information needed to reply directly to the user (e.g. after tool results are provided, or for general conversation), return:
{
  \"action\": \"respond\",
  \"response\": \"Your markdown response here\"
}

Available tools:
{$toolsDescription}";

        // Build message history for Ollama
        $dbMessages = $session->messages()->orderBy('created_at', 'asc')->get();
        
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ]
        ];

        foreach ($dbMessages as $msg) {
            if ($msg->role === 'user') {
                $messages[] = [
                    'role' => 'user',
                    'content' => $msg->content
                ];
            } elseif ($msg->role === 'assistant') {
                if ($msg->tool_calls) {
                    $toolCall = $msg->tool_calls[0] ?? null;
                    if ($toolCall) {
                        $messages[] = [
                            'role' => 'assistant',
                            'content' => json_encode([
                                'action' => 'tool_call',
                                'tool_name' => $toolCall['function']['name'] ?? '',
                                'arguments' => $toolCall['function']['arguments'] ?? []
                            ])
                        ];
                    }
                } else {
                    $messages[] = [
                        'role' => 'assistant',
                        'content' => json_encode([
                            'action' => 'respond',
                            'response' => $msg->content
                        ])
                    ];
                }
            } elseif ($msg->role === 'assistant_tool_call') {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $msg->content
                ];
            } elseif ($msg->role === 'tool') {
                $messages[] = [
                    'role' => 'user',
                    'content' => 'Tool result: ' . $msg->content
                ];
            }
        }
        
        // Loop to handle tool calls via prompt JSON parsing
        $maxLoops = 3;
        $finalResponse = null;

        for ($i = 0; $i < $maxLoops; $i++) {
            // Note: we pass empty tools array to avoid sending 'tools' payload, and request 'json' format
            $response = AgentService::chat($messages, [], 'json');

            if (isset($response['error'])) {
                return response()->json(['error' => $response['error']], 500);
            }

            $responseContent = $response['message']['content'] ?? '';
            $responseObj = json_decode($responseContent, true);

            if (!$responseObj) {
                // If it fails to parse JSON, treat content as raw response directly
                $finalResponse = $responseContent;
                ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'role' => 'assistant',
                    'content' => $finalResponse
                ]);
                break;
            }

            if (isset($responseObj['action']) && $responseObj['action'] === 'tool_call') {
                $toolName = $responseObj['tool_name'] ?? '';
                $toolArgs = $responseObj['arguments'] ?? [];

                // Save assistant message to DB with role 'assistant_tool_call'
                ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'role' => 'assistant_tool_call',
                    'content' => $responseContent
                ]);

                // Append to local messages array for next call
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $responseContent
                ];

                $toolResult = AiToolsService::handleToolCall($toolName, $toolArgs);
                $resultStr = is_string($toolResult) ? $toolResult : json_encode($toolResult);

                // Save tool result to DB with role 'tool'
                ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'role' => 'tool',
                    'content' => $resultStr
                ]);

                // Append tool result to messages array as user role
                $messages[] = [
                    'role' => 'user',
                    'content' => 'Tool result: ' . $resultStr
                ];
            } else {
                // Final response
                $finalResponse = $responseObj['response'] ?? $responseContent;
                
                // Save final text response
                ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'role' => 'assistant',
                    'content' => $finalResponse
                ]);

                break;
            }
        }

        return response()->json([
            'session_id' => $session->id,
            'message' => $finalResponse ?? "Sorry, I couldn't complete the request."
        ]);
    }

    public function destroySession($id)
    {
        $session = ChatSession::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $session->delete();
        return response()->json(['success' => true]);
    }
}
