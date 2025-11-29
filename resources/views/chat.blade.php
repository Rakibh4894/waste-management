
<!-- Floating Messenger Chat -->
<div id="chatbot-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <button id="chatbot-toggle" class="btn btn-success rounded-circle" 
            style="width:60px; height:60px; font-size:24px;">
        💬
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="card shadow-sm" 
         style="display:none; width:300px; height:400px; flex-direction:column;">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <span>Waste Bot</span>
            <button id="chatbot-close" class="btn btn-sm btn-light">&times;</button>
        </div>
        <div id="chatbot-messages" class="card-body overflow-auto d-flex flex-column" style="flex:1; padding:10px; gap:6px;">
            <div class="text-center text-muted small">Hi! How can I help you?</div>
        </div>
        <div class="card-footer p-2">
            <form id="chatbot-form" class="d-flex">
                <input type="text" id="chatbot-input" class="form-control form-control-sm me-1" placeholder="Enter you query..." required>
                <button type="submit" class="btn btn-success btn-sm">Send</button>
            </form>
        </div>
    </div>
</div>

<style>
    .message-bubble {
    max-width: 75%;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 14px;
    word-wrap: break-word;
}

.message-user {
    align-self: flex-end;
    background-color: #0d6efd;
    color: white;
    border-bottom-right-radius: 0;
}

.message-bot {
    align-self: flex-start;
    background-color: #e9ecef;
    color: #000;
    border-bottom-left-radius: 0;
}

</style>




<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const chatWindow = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close');
    const chatForm = document.getElementById('chatbot-form');
    const chatInput = document.getElementById('chatbot-input');
    const chatMessages = document.getElementById('chatbot-messages');

    toggleBtn.addEventListener('click', () => {
        chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
        chatWindow.style.flexDirection = 'column';
    });

    closeBtn.addEventListener('click', () => {
        chatWindow.style.display = 'none';
    });

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if(!msg) return;

        // User message
        const userBubble = document.createElement('div');
        userBubble.classList.add('message-bubble', 'message-user');
        userBubble.textContent = msg;
        chatMessages.appendChild(userBubble);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        chatInput.value = '';

        // Bot response via AJAX
        fetch("{{ url('/chatbot/message') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: msg })
        })
        .then(res => res.json())
        .then(data => {
            const botBubble = document.createElement('div');
            botBubble.classList.add('message-bubble', 'message-bot');
            botBubble.textContent = data.reply;
            chatMessages.appendChild(botBubble);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    });
});
</script>


