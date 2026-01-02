<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    .chatbot-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        height: 600px;
        overflow: hidden;
    }

    .chatbot-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        text-align: center;
        border-radius: 12px 12px 0 0;
    }

    .chatbot-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .chatbot-header p {
        margin: 5px 0 0 0;
        font-size: 13px;
        opacity: 0.9;
    }

    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message {
        display: flex;
        margin-bottom: 10px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.user {
        justify-content: flex-end;
    }

    .message.bot {
        justify-content: flex-start;
    }

    .message-content {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 12px;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .message.user .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message.bot .message-content {
        background: #e9ecef;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 12px 16px;
        background: #e9ecef;
        border-radius: 12px;
        width: fit-content;
    }

    .typing-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #999;
        animation: typing 1.4s infinite;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% {
            opacity: 0.5;
            transform: translateY(0);
        }
        30% {
            opacity: 1;
            transform: translateY(-10px);
        }
    }

    .chatbot-input-area {
        padding: 15px;
        background: white;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
        border-radius: 0 0 12px 12px;
    }

    .chatbot-input-area input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 24px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .chatbot-input-area input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .chatbot-input-area button {
        padding: 12px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 24px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .chatbot-input-area button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .chatbot-input-area button:active {
        transform: translateY(0);
    }

    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 12px 16px;
        border-radius: 8px;
        margin: 10px 0;
        font-size: 13px;
    }

    /* Scrollbar styling */
    .chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chatbot-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .chatbot-messages::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>


    </head>
<body>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="chatbot-container">
                <div class="chatbot-header">
                    <h2><i class="fas fa-shopping-cart"></i> Shopping Assistant</h2>
                    <p>Ask me anything about our products and services</p>
                </div>

                <div class="chatbot-messages" id="chatMessages">
                    <div class="message bot">
                        <div class="message-content">
                            👋 Hello @if(auth()->check()){{ auth()->user()->name }}@else User @endif! Welcome to your shopping assistant! 😊<br><br>
                            I'm here to help you with:
                            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                                <li>🔍 Finding products and deals</li>
                                <li>🛒 Shopping assistance and recommendations</li>
                                <li>📦 Orders, shipping, and returns</li>
                                <li>❓ Questions about our services</li>
                            </ul>
                            <br>How can I help make your shopping experience better today?
                        </div>
                    </div>
                    <div class="message bot" id="suggestionsContainer" style="display: none;">
                        <div class="message-content">
                            <strong>Suggested questions:</strong>
                            <div id="suggestionsBox" style="margin-top: 10px;"></div>
                        </div>
                    </div>
                </div>

                <div class="chatbot-input-area">
                    <input 
                        type="text" 
                        id="chatInput" 
                        placeholder="Type your question here..." 
                        onkeypress="if(event.key === 'Enter') sendMessage()"
                    >
                    <button onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let conversationHistory = [];

    // Load suggested questions on page load
    $(document).ready(function() {
        loadSuggestedQuestions();
    });

    function loadSuggestedQuestions() {
        $.ajax({
            url: '{{ route("chatbot.suggestions") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.suggestions && response.suggestions.length > 0) {
                    displaySuggestions(response.suggestions);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading suggestions:', error);
            }
        });
    }

    function displaySuggestions(suggestions) {
        const suggestionsBox = document.getElementById('suggestionsBox');
        const suggestionsContainer = document.getElementById('suggestionsContainer');
        
        suggestionsBox.innerHTML = '';
        
        suggestions.forEach(function(suggestion) {
            const button = document.createElement('button');
            button.className = 'suggestion-button';
            button.textContent = suggestion;
            button.onclick = function() {
                document.getElementById('chatInput').value = suggestion;
                sendMessage();
            };
            button.style.cssText = `
                display: block;
                width: 100%;
                padding: 8px 12px;
                margin: 5px 0;
                background: #f0f0f0;
                border: 1px solid #ddd;
                border-radius: 6px;
                cursor: pointer;
                text-align: left;
                font-size: 13px;
                transition: all 0.2s ease;
            `;
            button.onmouseover = function() {
                this.style.background = '#667eea';
                this.style.color = 'white';
                this.style.borderColor = '#667eea';
            };
            button.onmouseout = function() {
                this.style.background = '#f0f0f0';
                this.style.color = '#333';
                this.style.borderColor = '#ddd';
            };
            suggestionsBox.appendChild(button);
        });
        
        suggestionsContainer.style.display = 'flex';
    }

    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();

        if (!message) return;

        // Add user message to UI
        addMessageToUI(message, 'user');
        input.value = '';

        // Show typing indicator
        showTypingIndicator();

        // Send to backend
        $.ajax({
            url: '{{ route("chatbot.chat") }}',
            type: 'POST',
            data: {
                message: message,
                history: JSON.stringify(conversationHistory),
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response) {
                removeTypingIndicator();

                if (response.error) {
                    addMessageToUI('Sorry, I encountered an error: ' + response.error, 'bot');
                } else {
                    // Add bot response
                    addMessageToUI(response.reply, 'bot');

                    // Add to history
                    conversationHistory.push({ role: 'user', content: message });
                    conversationHistory.push({ role: 'assistant', content: response.reply });
                }
            },
            error: function(xhr, status, error) {
                removeTypingIndicator();
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                addMessageToUI('Sorry, I could not connect to the server. Please try again.', 'bot');
            }
        });
    }

    function addMessageToUI(text, sender) {
        const messagesDiv = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        // Format product list responses with proper line breaks
        if (sender === 'bot' && text.includes('Description:') && text.includes('Link:')) {
            // Split by product pattern and format
            const formatted = formatProductResponse(text);
            contentDiv.innerHTML = formatted;
        } else if (sender === 'bot' && (text.includes('<br>') || text.includes('😊'))) {
            // Handle HTML content in bot messages (like greetings)
            contentDiv.innerHTML = text;
        } else {
            contentDiv.textContent = text;
        }

        messageDiv.appendChild(contentDiv);
        messagesDiv.appendChild(messageDiv);

        // Scroll to bottom
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function formatProductResponse(text) {
        // Replace product patterns with proper HTML formatting
        // Pattern: "1. Product Name - ₹Price Description: desc Link: url"
        
        let formatted = text;
        
        // Add line breaks before "Description:" and "Link:"
        formatted = formatted.replace(/Description:/g, '<br>   Description:');
        formatted = formatted.replace(/Link:/g, '<br>   Link:');
        
        // Add line breaks before numbered items (but not the first one)
        formatted = formatted.replace(/(\d+)\. /g, '<br><br>$1. ');
        
        // Remove the first <br><br> if it exists
        if (formatted.startsWith('<br><br>')) {
            formatted = formatted.substring(8);
        }
        
        // Convert URLs to clickable links
        formatted = formatted.replace(/(http[s]?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" style="color: #007bff; text-decoration: none;">$1</a>');
        
        // Add line breaks for closing message
        formatted = formatted.replace(/Do you need/g, '<br><br>Do you need');
        formatted = formatted.replace(/Would you like/g, '<br><br>Would you like');
        
        return formatted;
    }

    function showTypingIndicator() {
        const messagesDiv = document.getElementById('chatMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        messagesDiv.appendChild(typingDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
    }
</script>
