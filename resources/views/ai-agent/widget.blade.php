<style>
/* AI Widget Styling */
#ai-widget-bubble {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--primary, #6366f1);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
    cursor: pointer;
    z-index: 1045;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
}
#ai-widget-bubble:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
}
#ai-widget-bubble i {
    font-size: 24px;
}

#ai-widget-container {
    position: fixed;
    bottom: 80px;
    right: 20px;
    width: 380px;
    height: 480px;
    min-width: 300px;
    min-height: 350px;
    max-width: 95vw;
    max-height: 90vh;
    background: var(--surface, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    display: flex;
    flex-direction: column;
    z-index: 1045;
    overflow: hidden;
    transition: box-shadow 0.2s;
}

/* Glassmorphism support in Dark Mode */
[data-bs-theme="dark"] #ai-widget-container {
    background: rgba(30, 41, 59, 0.85);
    backdrop-filter: blur(12px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

/* Resizers */
.ai-widget-resizer {
    position: absolute;
    background: transparent;
    z-index: 1055;
}
.ai-widget-resizer.resizer-t {
    top: 0; left: 0; right: 0; height: 6px; cursor: ns-resize;
}
.ai-widget-resizer.resizer-l {
    top: 0; bottom: 0; left: 0; width: 6px; cursor: ew-resize;
}
.ai-widget-resizer.resizer-tl {
    top: 0; left: 0; width: 14px; height: 14px; cursor: nwse-resize;
}

/* Header */
.ai-widget-header {
    padding: 14px 16px;
    background: var(--table-th-bg, #f8fafc);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
}
.ai-widget-header-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: var(--text-main, #1e293b);
}
.ai-widget-header-actions {
    display: flex;
    align-items: center;
    gap: 4px;
}
.ai-widget-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    color: var(--text-muted, #64748b);
    cursor: pointer;
    transition: all 0.2s;
}
.ai-widget-btn:hover {
    background: rgba(100, 116, 139, 0.1);
    color: var(--text-main, #1e293b);
}
.ai-widget-btn.btn-close-widget:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Body & Message log */
.ai-widget-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}
.ai-widget-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ai-widget-messages::-webkit-scrollbar {
    width: 6px;
}
.ai-widget-messages::-webkit-scrollbar-track {
    background: transparent;
}
.ai-widget-messages::-webkit-scrollbar-thumb {
    background: rgba(100, 116, 139, 0.2);
    border-radius: 3px;
}
.ai-widget-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.4);
}

.ai-msg {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 0.9rem;
    line-height: 1.4;
    word-break: break-word;
}
.ai-msg p:last-child {
    margin-bottom: 0;
}
.ai-msg.user {
    align-self: flex-end;
    background: var(--primary, #6366f1);
    color: #fff;
    border-bottom-right-radius: 2px;
}
.ai-msg.assistant {
    align-self: flex-start;
    background: var(--table-hover-bg, #f1f5f9);
    color: var(--text-main, #1e293b);
    border-bottom-left-radius: 2px;
    border: 1px solid var(--border-color, #e2e8f0);
}
.ai-msg table {
    width: 100%;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
    border-collapse: collapse;
}
.ai-msg table th, .ai-msg table td {
    padding: 0.4rem;
    border: 1px solid var(--border-color, #e2e8f0);
}

/* Typing Indicator */
.ai-widget-typing {
    padding: 10px 16px;
    display: none;
    align-self: flex-start;
    font-size: 0.85rem;
    color: var(--text-muted, #64748b);
    align-items: center;
    gap: 6px;
}
.ai-typing-dots {
    display: flex;
    gap: 3px;
}
.ai-typing-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--text-muted, #64748b);
    animation: ai-dot-blink 1.4s infinite both;
}
.ai-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.ai-typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes ai-dot-blink {
    0%, 80%, 100% { opacity: 0.2; }
    40% { opacity: 1; }
}

/* Footer & Input */
.ai-widget-footer {
    padding: 12px;
    border-top: 1px solid var(--border-color, #e2e8f0);
    background: var(--surface, #ffffff);
}
.ai-widget-input-group {
    display: flex;
    gap: 8px;
}
.ai-widget-input {
    flex: 1;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.9rem;
    background: var(--surface, #ffffff);
    color: var(--text-main, #1e293b);
    outline: none;
    transition: border-color 0.2s;
}
.ai-widget-input:focus {
    border-color: var(--primary, #6366f1);
}
.ai-widget-send-btn {
    background: var(--primary, #6366f1);
    color: #fff;
    border: none;
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: opacity 0.2s;
}
.ai-widget-send-btn:hover {
    opacity: 0.9;
}
.ai-widget-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<!-- Floating Bubble -->
<button id="ai-widget-bubble" title="Open AI Assistant">
    <i class="fa-solid fa-robot"></i>
</button>

<!-- Floating Chat Box -->
<div id="ai-widget-container" style="display: none;">
    <!-- Resizer drag handles -->
    <div class="ai-widget-resizer resizer-t"></div>
    <div class="ai-widget-resizer resizer-l"></div>
    <div class="ai-widget-resizer resizer-tl"></div>

    <!-- Header -->
    <div class="ai-widget-header">
        <div class="ai-widget-header-title">
            <i class="fa-solid fa-robot text-primary"></i>
            <span>AI Assistant</span>
        </div>
        <div class="ai-widget-header-actions">
            <button class="ai-widget-btn" id="ai-widget-enlarge" title="Enlarge Window">
                <i class="fa-solid fa-expand"></i>
            </button>
            <button class="ai-widget-btn" id="ai-widget-minimize" title="Minimize to Bubble">
                <i class="fa-solid fa-minus"></i>
            </button>
            <button class="ai-widget-btn btn-close-widget" id="ai-widget-close" title="Close Widget">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- Body -->
    <div class="ai-widget-body">
        <div class="ai-widget-messages" id="ai-widget-messages">
            <div class="text-center text-muted my-auto py-4">
                <i class="fa-solid fa-robot fa-2x mb-2 text-primary opacity-50"></i>
                <p class="mb-0 fw-semibold">AI Assistant ready</p>
                <small class="text-muted">Ask about attendance, workloads, or team tasks!</small>
            </div>
        </div>
        <div class="ai-widget-typing" id="ai-widget-typing">
            <div class="ai-typing-dots">
                <div class="ai-typing-dot"></div>
                <div class="ai-typing-dot"></div>
                <div class="ai-typing-dot"></div>
            </div>
            <span>Assistant is writing...</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="ai-widget-footer">
        <form id="ai-widget-form" class="m-0" onsubmit="event.preventDefault(); sendWidgetMessage();">
            <div class="ai-widget-input-group">
                <input type="text" id="ai-widget-input" class="ai-widget-input" placeholder="Type a message..." autocomplete="off" required>
                <button type="submit" id="ai-widget-send" class="ai-widget-send-btn">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    const bubble = $('#ai-widget-bubble');
    const container = $('#ai-widget-container');
    const input = $('#ai-widget-input');
    const messagesContainer = $('#ai-widget-messages');
    const widgetRoutePrefix = '{{ auth()->user()->role === "admin" ? "/admin/ai-agent" : "/employee/ai-agent" }}';
    
    let isResizing = false;
    let currentResizer = null;
    let lastLoadedSessionId = null;

    // Hoisted function statements
    function setWidgetState(state) {
        localStorage.setItem('ai_assistant_state', state);
        if (state === 'open') {
            container.fadeIn(200);
            bubble.fadeOut(200);
            scrollToBottomWidget();
            loadActiveSessionMessages();
        } else if (state === 'minimized') {
            container.fadeOut(200);
            bubble.fadeIn(200);
        } else if (state === 'closed') {
            container.fadeOut(200);
            bubble.fadeOut(200);
        }
    }

    function loadActiveSessionMessages() {
        let activeSessionId = localStorage.getItem('active_chat_session_id');

        if (activeSessionId === 'new') {
            lastLoadedSessionId = 'new';
            // Empty state for new chat
            messagesContainer.html(`
                <div class="text-center text-muted my-auto py-4">
                    <i class="fa-solid fa-robot fa-2x mb-2 text-primary opacity-50"></i>
                    <p class="mb-0 fw-semibold">Start a Conversation</p>
                    <small class="text-muted">Type your first message below to begin.</small>
                </div>
            `);
            return;
        }

        if (!activeSessionId) {
            // Try fetching latest session from database list
            fetch(`${widgetRoutePrefix}/sessions`)
                .then(res => {
                    if (!res.ok) throw new Error();
                    return res.json();
                })
                .then(sessions => {
                    if (sessions.length > 0) {
                        localStorage.setItem('active_chat_session_id', sessions[0].id);
                        loadActiveSessionMessages();
                    } else {
                        // Empty state (no sessions yet)
                        messagesContainer.html(`
                            <div class="text-center text-muted my-auto py-4">
                                <i class="fa-solid fa-robot fa-2x mb-2 text-primary opacity-50"></i>
                                <p class="mb-0 fw-semibold">Start a Conversation</p>
                                <small class="text-muted">Type your first message below to begin.</small>
                            </div>
                        `);
                    }
                })
                .catch(() => {
                    // Fail gracefully to new state
                    localStorage.setItem('active_chat_session_id', 'new');
                    lastLoadedSessionId = 'new';
                    messagesContainer.html(`
                        <div class="text-center text-muted my-auto py-4">
                            <i class="fa-solid fa-robot fa-2x mb-2 text-primary opacity-50"></i>
                            <p class="mb-0 fw-semibold">Start a Conversation</p>
                            <small class="text-muted">Type your first message below to begin.</small>
                        </div>
                    `);
                });
            return;
        }

        if (lastLoadedSessionId === activeSessionId) return; // avoid duplicate fetching
        lastLoadedSessionId = activeSessionId;

        messagesContainer.html('<div class="text-center text-muted my-auto"><i class="fa-solid fa-spinner fa-spin fa-lg text-primary mb-2"></i><p class="small">Loading messages...</p></div>');

        fetch(`${widgetRoutePrefix}/sessions/${activeSessionId}`)
            .then(res => {
                if (!res.ok) throw new Error();
                return res.json();
            })
            .then(data => {
                messagesContainer.empty();
                if (data.messages.length === 0) {
                    messagesContainer.html('<div class="text-center text-muted my-auto">No messages yet.</div>');
                    return;
                }

                data.messages.forEach(msg => {
                    if (msg.role === 'user' || msg.role === 'assistant') {
                        if (msg.content) appendWidgetMessage(msg.role, msg.content, false);
                    }
                });
                scrollToBottomWidget();
            })
            .catch(() => {
                // If the session fails to load (e.g. 404 or 403 because it belongs to another user/role), reset to 'new'
                localStorage.setItem('active_chat_session_id', 'new');
                lastLoadedSessionId = 'new';
                messagesContainer.html(`
                    <div class="text-center text-muted my-auto py-4">
                        <i class="fa-solid fa-robot fa-2x mb-2 text-primary opacity-50"></i>
                        <p class="mb-0 fw-semibold">Start a Conversation</p>
                        <small class="text-muted">Type your first message below to begin.</small>
                    </div>
                `);
            });
    }

    // Expose loadActiveSessionMessages globally so chat.blade.php can access it if needed
    window.loadActiveSessionMessages = loadActiveSessionMessages;

    function sendWidgetMessage() {
        const text = input.val().trim();
        if (!text) return;

        // Clear empty state
        if (messagesContainer.find('.text-center').length) {
            messagesContainer.empty();
        }

        appendWidgetMessage('user', text, true);
        input.val('');
        $('#ai-widget-send').prop('disabled', true);
        $('#ai-widget-typing').css('display', 'flex');
        scrollToBottomWidget();

        const activeSessionId = localStorage.getItem('active_chat_session_id');

        fetch(`${widgetRoutePrefix}/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                message: text,
                session_id: activeSessionId === 'new' ? null : activeSessionId
            })
        })
        .then(res => {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then(data => {
            $('#ai-widget-typing').hide();
            $('#ai-widget-send').prop('disabled', false);

            if (data.error) {
                appendWidgetMessage('assistant', `**Error:** ${data.error}`, true);
                return;
            }

            if ((!activeSessionId || activeSessionId === 'new') && data.session_id) {
                localStorage.setItem('active_chat_session_id', data.session_id);
                lastLoadedSessionId = data.session_id;
            }

            appendWidgetMessage('assistant', data.message, true);
        })
        .catch(() => {
            $('#ai-widget-typing').hide();
            $('#ai-widget-send').prop('disabled', false);
            appendWidgetMessage('assistant', `**Error:** Failed to connect to server.`, true);
        });
    }
    // Expose sendWidgetMessage globally for form submit
    window.sendWidgetMessage = sendWidgetMessage;

    function appendWidgetMessage(role, content, scroll = true) {
        const div = $('<div class="ai-msg"></div>').addClass(role);
        if (role === 'assistant') {
            // Render markdown using Marked.js
            div.html(marked.parse(content));
            
            // Format tables
            div.find('table').addClass('table table-bordered table-sm');
            if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
                div.find('table').addClass('table-dark');
            }
        } else {
            // Escape User Input text
            div.text(content);
        }
        
        messagesContainer.append(div);
        if (scroll) scrollToBottomWidget();
    }

    function scrollToBottomWidget() {
        if (messagesContainer[0]) {
            messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
        }
    }

    // Set custom sizes if persisted
    const savedWidth = localStorage.getItem('ai_assistant_width');
    const savedHeight = localStorage.getItem('ai_assistant_height');
    if (savedWidth) container.css('width', savedWidth + 'px');
    if (savedHeight) container.css('height', savedHeight + 'px');

    // Register Event Handlers
    bubble.on('click', () => setWidgetState('open'));
    $('#ai-widget-minimize').on('click', () => setWidgetState('minimized'));
    $('#ai-widget-close').on('click', () => setWidgetState('closed'));
    
    $('#aiAssistantToggle').on('click', function(e) {
        e.preventDefault();
        setWidgetState('open');
    });

    // Enlarge & Restore Toggle
    $('#ai-widget-enlarge').on('click', function() {
        const isEnlarged = container.hasClass('ai-widget-enlarged');
        if (isEnlarged) {
            container.removeClass('ai-widget-enlarged');
            $(this).html('<i class="fa-solid fa-expand"></i>');
            const w = localStorage.getItem('ai_assistant_width') || 380;
            const h = localStorage.getItem('ai_assistant_height') || 480;
            container.css({ width: w + 'px', height: h + 'px' });
        } else {
            container.addClass('ai-widget-enlarged');
            $(this).html('<i class="fa-solid fa-compress"></i>');
            
            // Enlarge but respect screen boundaries
            const targetWidth = Math.min(750, window.innerWidth - 40);
            const targetHeight = Math.min(600, window.innerHeight - 100);
            container.css({ width: targetWidth + 'px', height: targetHeight + 'px' });
        }
        scrollToBottomWidget();
    });

    // Drag Resizing logic
    $('.ai-widget-resizer').on('mousedown', function(e) {
        isResizing = true;
        currentResizer = $(this);
        $('body').css('user-select', 'none');
        e.preventDefault();
    });

    $(document).on('mousemove', function(e) {
        if (!isResizing) return;

        if (container.hasClass('ai-widget-enlarged')) {
            container.removeClass('ai-widget-enlarged');
            $('#ai-widget-enlarge').html('<i class="fa-solid fa-expand"></i>');
        }

        let newWidth = container.width();
        let newHeight = container.height();

        if (currentResizer.hasClass('resizer-l') || currentResizer.hasClass('resizer-tl')) {
            const rightEdge = window.innerWidth - 20;
            newWidth = rightEdge - e.clientX;
        }

        if (currentResizer.hasClass('resizer-t') || currentResizer.hasClass('resizer-tl')) {
            const bottomEdge = window.innerHeight - 80;
            newHeight = bottomEdge - e.clientY;
        }

        // Constraints
        const minW = 300;
        const minH = 350;
        const maxW = window.innerWidth - 40;
        const maxH = window.innerHeight - 100;

        newWidth = Math.max(minW, Math.min(newWidth, maxW));
        newHeight = Math.max(minH, Math.min(newHeight, maxH));

        container.css({
            width: newWidth + 'px',
            height: newHeight + 'px'
        });

        // Persist dimensions
        localStorage.setItem('ai_assistant_width', newWidth);
        localStorage.setItem('ai_assistant_height', newHeight);
    });

    $(document).on('mouseup', function() {
        if (isResizing) {
            isResizing = false;
            currentResizer = null;
            $('body').css('user-select', '');
            scrollToBottomWidget();
        }
    });

    // Check active session periodically
    setInterval(() => {
        const currentId = localStorage.getItem('active_chat_session_id');
        if (currentId !== lastLoadedSessionId) {
            loadActiveSessionMessages();
        }
    }, 1000);

    // Initialize State
    const initialState = localStorage.getItem('ai_assistant_state') || 'open';
    setWidgetState(initialState);
});
</script>
