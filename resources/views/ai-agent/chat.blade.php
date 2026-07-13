@extends('master')

@push('page-style')
<style>
.chat-container { 
    display: flex; 
    height: calc(100vh - 160px); 
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    background: var(--surface);
}
.chat-sidebar { 
    width: 300px; 
    border-right: 1px solid var(--border-color); 
    overflow-y: auto; 
    background-color: var(--table-th-bg); 
    display: flex;
    flex-direction: column;
}
.chat-main { 
    flex: 1; 
    display: flex; 
    flex-direction: column; 
}
.chat-messages { 
    flex: 1; 
    overflow-y: auto; 
    padding: 24px; 
}
.chat-input-area { 
    padding: 20px; 
    border-top: 1px solid var(--border-color); 
}
.message { 
    max-width: 80%; 
    margin-bottom: 20px; 
    padding: 16px; 
    border-radius: 12px; 
    clear: both; 
    line-height: 1.5;
}
.message p:last-child { margin-bottom: 0; }
.message.user { 
    float: right; 
    background-color: var(--primary); 
    color: white; 
    border-bottom-right-radius: 0; 
}
.message.assistant { 
    float: left; 
    background-color: var(--table-hover-bg); 
    color: var(--text-main); 
    border-bottom-left-radius: 0; 
    border: 1px solid var(--border-color);
}
.message.tool {
    float: left;
    background-color: var(--table-th-bg);
    color: var(--text-muted);
    font-size: 0.85em;
    font-family: monospace;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    width: 100%;
    white-space: pre-wrap;
    display: none; /* Hidden by default to not clutter UI, can be toggled if needed */
}
.session-item { 
    padding: 12px 20px; 
    cursor: pointer; 
    border-bottom: 1px solid var(--border-color); 
    color: var(--text-main);
    transition: background 0.2s;
}
.session-item:hover, .session-item.active { 
    background-color: rgba(99, 102, 241, 0.1); 
    border-left: 4px solid var(--primary);
}
.session-item:not(.active) {
    border-left: 4px solid transparent;
}
.typing-indicator { 
    display: none; 
    padding: 10px 24px; 
    font-style: italic; 
    color: var(--text-muted); 
    clear: both;
}
/* Markdown Table Styles */
.message table {
    width: 100%;
    margin-bottom: 1rem;
    color: var(--text-main);
    border-collapse: collapse;
}
.message table th,
.message table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid var(--border-color);
}
.message table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid var(--border-color);
}
</style>
@endpush

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="header-title mb-0">
        <i class="fa-solid fa-robot text-primary me-2"></i> AI Chatbot
    </h4>
</div>

<div class="chat-container shadow-sm">
    <div class="chat-sidebar" id="chat-sidebar">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Chat History</h6>
            <button class="btn btn-primary btn-sm" id="new-chat-btn" title="New Chat">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
        <div id="sessions-list" style="flex: 1; overflow-y: auto;">
            <!-- Session list populated by JS -->
        </div>
    </div>
    
    <div class="chat-main">
        <div class="chat-messages" id="chat-messages">
            <div class="text-center text-muted mt-5">
                <i class="fa-solid fa-robot fa-3x mb-3 text-primary opacity-50"></i>
                @if(auth()->user()->role === 'admin')
                    <h5>How can I help you manage the team today?</h5>
                @else
                    <h5>How can I help you with your work details today?</h5>
                @endif
                <p>Select a chat from the sidebar or type a new message below.</p>
            </div>
        </div>
        <div class="typing-indicator" id="typing-indicator">
            <i class="fa-solid fa-circle-notch fa-spin me-2"></i> AI is thinking...
        </div>
        <div class="chat-input-area">
            <form id="chat-form" class="d-flex">
                @csrf
                <input type="text" id="chat-input" class="form-control me-3" placeholder="Ask about top employees, team performance..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary px-4" id="send-btn">
                    <i class="fa-solid fa-paper-plane me-1"></i> Send
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-script')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    const routePrefix = '{{ auth()->user()->role === "admin" ? "/admin/ai-agent" : "/employee/ai-agent" }}';
    let currentSessionId = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Retrieve initial session from localStorage if present
        const savedSessionId = localStorage.getItem('active_chat_session_id');
        if (savedSessionId && savedSessionId !== 'new') {
            currentSessionId = parseInt(savedSessionId) || savedSessionId;
            loadSessionMessages(currentSessionId);
        } else if (savedSessionId === 'new') {
            currentSessionId = 'new';
            document.getElementById('chat-messages').innerHTML = `
                <div class="text-center text-muted mt-5">
                    <i class="fa-solid fa-robot fa-3x mb-3 text-primary opacity-50"></i>
                    <h5>New Chat Started</h5>
                    <p>What would you like to know?</p>
                </div>
            `;
        }

        loadSessions();

        document.getElementById('new-chat-btn').addEventListener('click', function() {
            currentSessionId = 'new';
            localStorage.setItem('active_chat_session_id', 'new');
            document.getElementById('chat-messages').innerHTML = `
                <div class="text-center text-muted mt-5">
                    <i class="fa-solid fa-robot fa-3x mb-3 text-primary opacity-50"></i>
                    <h5>New Chat Started</h5>
                    <p>What would you like to know?</p>
                </div>
            `;
            document.querySelectorAll('.session-item').forEach(el => el.classList.remove('active'));
            document.getElementById('chat-input').focus();
        });

        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    });

    function loadSessions() {
        fetch(`${routePrefix}/sessions`)
            .then(res => {
                if (!res.ok) throw new Error();
                return res.json();
            })
            .then(data => {
                const sidebar = document.getElementById('sessions-list');
                sidebar.innerHTML = '';
                data.forEach(session => {
                    const div = document.createElement('div');
                    div.className = 'session-item d-flex justify-content-between align-items-center';
                    if (session.id == currentSessionId) div.classList.add('active');
                    div.innerHTML = `
                        <span class="text-truncate" style="max-width: 200px; font-weight: 500;">${session.title}</span>
                        <button class="btn btn-sm text-danger delete-session p-1 border-0 bg-transparent" data-id="${session.id}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                    div.addEventListener('click', (e) => {
                        if (e.target.closest('.delete-session')) {
                            deleteSession(session.id);
                            return;
                        }
                        currentSessionId = session.id;
                        localStorage.setItem('active_chat_session_id', session.id);
                        loadSessionMessages(session.id);
                        document.querySelectorAll('.session-item').forEach(el => el.classList.remove('active'));
                        div.classList.add('active');
                    });
                    sidebar.appendChild(div);
                });
            });
    }

    function loadSessionMessages(id) {
        document.getElementById('chat-messages').innerHTML = '<div class="text-center text-muted my-5"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading messages...</p></div>';
        fetch(`${routePrefix}/sessions/${id}`)
            .then(res => {
                if (!res.ok) throw new Error();
                return res.json();
            })
            .then(data => {
                const container = document.getElementById('chat-messages');
                container.innerHTML = '';
                
                if (data.messages.length === 0) {
                    container.innerHTML = '<div class="text-center text-muted my-5">No messages yet.</div>';
                    return;
                }

                data.messages.forEach(msg => {
                    if (msg.role === 'user' || msg.role === 'assistant') {
                        if (msg.content) appendMessage(msg.role, msg.content, false);
                    }
                });
                scrollToBottom();
            })
            .catch(() => {
                // If loading fails, clear active session and show start screen
                currentSessionId = 'new';
                localStorage.setItem('active_chat_session_id', 'new');
                document.getElementById('chat-messages').innerHTML = `
                    <div class="text-center text-muted mt-5">
                        <i class="fa-solid fa-robot fa-3x mb-3 text-primary opacity-50"></i>
                        @if(auth()->user()->role === 'admin')
                            <h5>Start a Conversation</h5>
                        @else
                            <h5>New Chat Started</h5>
                        @endif
                        <p>What would you like to know?</p>
                    </div>
                `;
            });
    }

    function deleteSession(id) {
        if (!confirm('Are you sure you want to delete this chat history?')) return;
        fetch(`${routePrefix}/sessions/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        }).then(() => {
            if (currentSessionId == id) {
                currentSessionId = null;
                localStorage.removeItem('active_chat_session_id');
                document.getElementById('chat-messages').innerHTML = `
                    <div class="text-center text-muted mt-5">
                        <i class="fa-solid fa-robot fa-3x mb-3 text-primary opacity-50"></i>
                        <h5>Chat Deleted</h5>
                        <p>Select another chat or start a new one.</p>
                    </div>
                `;
            }
            loadSessions();
        });
    }

    function sendMessage() {
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text) return;

        // Clear empty state if needed
        const container = document.getElementById('chat-messages');
        if (container.querySelector('.text-center')) {
            container.innerHTML = '';
        }

        appendMessage('user', text, true);
        input.value = '';
        document.getElementById('send-btn').disabled = true;
        document.getElementById('typing-indicator').style.display = 'block';
        scrollToBottom();

        fetch(`${routePrefix}/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
                message: text,
                session_id: currentSessionId === 'new' ? null : currentSessionId
            })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('typing-indicator').style.display = 'none';
            document.getElementById('send-btn').disabled = false;
            
            if (data.error) {
                appendMessage('assistant', `**Error:** ${data.error}`, true);
                return;
            }

            if ((!currentSessionId || currentSessionId === 'new') && data.session_id) {
                currentSessionId = data.session_id;
                localStorage.setItem('active_chat_session_id', data.session_id);
                loadSessions();
            }

            appendMessage('assistant', data.message, true);
        })
        .catch(err => {
            document.getElementById('typing-indicator').style.display = 'none';
            document.getElementById('send-btn').disabled = false;
            appendMessage('assistant', `**Error:** Failed to communicate with the server.`, true);
        });
    }

    // Sync loop: check if active session changes in widget
    let lastKnownLocalSessionId = localStorage.getItem('active_chat_session_id');
    setInterval(() => {
        const currentLocalId = localStorage.getItem('active_chat_session_id');
        if (currentLocalId !== lastKnownLocalSessionId) {
            lastKnownLocalSessionId = currentLocalId;
            currentSessionId = currentLocalId ? (currentLocalId === 'new' ? 'new' : (parseInt(currentLocalId) || currentLocalId)) : null;
            if (currentSessionId && currentSessionId !== 'new') {
                loadSessionMessages(currentSessionId);
            } else if (currentSessionId === 'new') {
                document.getElementById('chat-messages').innerHTML = `
                    <div class="text-center text-muted mt-5">
                        <i class="fa-solid fa-robot fa-3x mb-3 text-primary opacity-50"></i>
                        <h5>New Chat Started</h5>
                        <p>What would you like to know?</p>
                    </div>
                `;
            } else {
                document.getElementById('chat-messages').innerHTML = `
                    <div class="text-center text-muted mt-5">
                        <i class="fa-solid fa-robot fa-3x mb-3 text-primary opacity-50"></i>
                        <h5>No Active Chat</h5>
                        <p>Select a chat from history or start a new one.</p>
                    </div>
                `;
            }
            loadSessions();
        }
    }, 1000);

    function appendMessage(role, content, scrollToBottomFlag = true) {
        const container = document.getElementById('chat-messages');
        const div = document.createElement('div');
        div.className = `message ${role}`;
        
        if (role === 'assistant') {
            // Render Markdown
            div.innerHTML = marked.parse(content);
            
            // Add bootstrap table classes to generated tables
            div.querySelectorAll('table').forEach(table => {
                table.classList.add('table', 'table-bordered', 'table-sm');
                if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
                     table.classList.add('table-dark');
                }
            });
        } else {
            // Escape HTML for user input
            const p = document.createElement('p');
            p.textContent = content;
            p.className = 'mb-0';
            div.appendChild(p);
        }
        
        container.appendChild(div);
        
        // Clear floats
        const clearFix = document.createElement('div');
        clearFix.style.clear = 'both';
        container.appendChild(clearFix);

        if (scrollToBottomFlag) {
            scrollToBottom();
        }
    }

    function scrollToBottom() {
        const container = document.getElementById('chat-messages');
        container.scrollTop = container.scrollHeight;
    }
</script>
@endpush
