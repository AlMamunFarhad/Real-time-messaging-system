<x-messaging::layouts.master>

    @php
        $participantId = \Modules\Messaging\Helpers\AuthParticipant::id();
        $participantType = \Modules\Messaging\Helpers\AuthParticipant::type();
        $participantTypeShort = strtolower(class_basename($participantType));

        $participants = $conversation->participants ?? collect();

        $otherParticipantId = $otherParticipant ? $otherParticipant->participant_id : 0;
        $otherTypeShort = '';
        if ($otherParticipant) {
            $resolvedOtherType = match ($otherParticipant->participant_type) {
                'admin' => \App\Models\Admin::class,
                'user' => \App\Models\User::class,
                default => $otherParticipant->participant_type,
            };

            $otherTypeShort = strtolower(class_basename($resolvedOtherType));
        }
    @endphp

    <div style="width: 100%; max-width: 900px; margin: 1.5rem auto; background: #ffffff; border-radius: 24px; border: 1px solid rgba(15, 23, 42, 0.08); overflow: hidden; box-shadow: 0 20px 40px -12px rgba(15, 23, 42, 0.12);">

        <!-- Header -->
        <div style="background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(251,146,60,0.15); color: #44403c; padding: 1.25rem 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="height: 48px; width: 48px; border-radius: 16px; background: linear-gradient(135deg, #fb7185 0%, #fdba74 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(251, 113, 133, 0.25);">
                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: white;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-weight: 700; font-size: 1.15rem; margin: 0; color: #44403c; letter-spacing: -0.01em;">{{ $otherUserName }}</h3>
                        <p style="font-size: 0.75rem; color: #a8a29e; margin: 0; margin-top: 2px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Conversation #{{ $conversation->id }}</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div id="online-status" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(251,146,60,0.05); padding: 0.4rem 0.8rem; border-radius: 20px; border: 1px solid rgba(251,146,60,0.1);">
                        <span id="online-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #d6d3d1; box-shadow: 0 0 8px rgba(214, 211, 209, 0.5); transition: background 0.3s ease;"></span>
                        <span id="online-text" style="color: #a8a29e; transition: color 0.3s ease;">Offline</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Body -->
        <div id="chat-box" style="height: 65vh; min-height: 450px; max-height: 65vh; overflow-y: auto; padding: 1.5rem; background: radial-gradient(ellipse at top right, rgba(255,237,213,0.5), #ffffff, rgba(255,228,230,0.3));">
            <!-- Skeleton Loader -->
            <div id="chat-skeleton" style="display: none;">
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: flex; justify-content: flex-start; animation: fadeInUp 0.4s ease-out;">
                        <div style="width: 65%; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="height: 12px; width: 80px; background: rgba(255,237,213,0.8); border-radius: 4px; animation: pulse 2s infinite;"></div>
                            <div style="height: 50px; width: 100%; background: white; border-radius: 20px; border-bottom-left-radius: 6px; box-shadow: 0 4px 20px -4px rgba(251,146,60,0.05); border: 1px solid rgba(255,237,213,0.5); animation: pulse 2s infinite;"></div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; animation: fadeInUp 0.5s ease-out;">
                        <div style="width: 50%; display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                            <div style="height: 40px; width: 100%; background: linear-gradient(135deg, rgba(244,63,94,0.4) 0%, rgba(251,146,60,0.4) 100%); border-radius: 20px; border-bottom-right-radius: 6px; animation: pulse 2s infinite;"></div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-start; animation: fadeInUp 0.6s ease-out;">
                        <div style="width: 40%; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="height: 12px; width: 60px; background: rgba(255,237,213,0.8); border-radius: 4px; animation: pulse 2s infinite;"></div>
                            <div style="height: 60px; width: 100%; background: white; border-radius: 20px; border-bottom-left-radius: 6px; box-shadow: 0 4px 20px -4px rgba(251,146,60,0.05); border: 1px solid rgba(255,237,213,0.5); animation: pulse 2s infinite;"></div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; animation: fadeInUp 0.7s ease-out;">
                        <div style="width: 55%; display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                            <div style="height: 45px; width: 100%; background: linear-gradient(135deg, rgba(244,63,94,0.4) 0%, rgba(251,146,60,0.4) 100%); border-radius: 20px; border-bottom-right-radius: 6px; animation: pulse 2s infinite;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="empty-state" style="text-align: center; color: #fb923c; padding: 2rem; font-size: 14px;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 56px; height: 56px; margin: 0 auto 16px auto; opacity: 0.3; color: #f43f5e;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p style="font-weight: 500; font-size: 16px; color: #44403c;">Start your exciting conversation</p>
            </div>
        </div>

        <!-- Input -->
        <div style="border-top: 1px solid rgba(251,146,60,0.15); background: rgba(255,255,255,0.7); backdrop-filter: blur(16px); padding: 1.25rem 1.5rem; box-shadow: 0 -10px 40px -5px rgba(251,146,60,0.05);">
            <!-- File Preview -->
            <div id="file-preview" style="display: none; margin-bottom: 0.75rem; padding: 0.75rem; background: rgba(251,146,60,0.05); border: 1px solid rgba(251,146,60,0.15); border-radius: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span id="file-name" style="font-size: 0.875rem; color: #44403c; font-weight: 600; display: flex; align-items: center; gap: 8px;"></span>
                    <button type="button" onclick="removeFile()" style="background: white; border: none; color: #a8a29e; cursor: pointer; font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.2s;" onmouseover="this.style.color='#f43f5e'; this.style.background='#fff0f2'" onmouseout="this.style.color='#a8a29e'; this.style.background='white'">&times;</button>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <input type="file" id="file-input" style="display: none;" onchange="handleFileSelect(event)">
                <button type="button" onclick="document.getElementById('file-input').click()" style="background: #fff7ed; color: #f97316; width: 52px; height: 52px; border-radius: 20px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(251,146,60,0.1); transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)'; this.style.background='#ffedd5'" onmouseout="this.style.transform='scale(1)'; this.style.background='#fff7ed'">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </button>
                <input type="text" id="message-input" style="flex: 1; background: white; border: 1px solid rgba(251,146,60,0.2); border-radius: 24px; padding: 0 1.25rem; height: 52px; color: #44403c; font-size: 0.9375rem; outline: none; box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: all 0.2s;" placeholder="Type your message..." autocomplete="off" onfocus="this.style.borderColor='#fda4af'; this.style.boxShadow='0 0 0 4px rgba(254,205,211,0.5)'" onblur="this.style.borderColor='rgba(251,146,60,0.2)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.02)'">
                <button type="button" id="voice-record-btn" onclick="toggleVoiceRecording()" title="Record Voice Message" style="background: #fff1f2; color: #e11d48; width: 52px; height: 52px; flex-shrink: 0; border-radius: 20px; border: 1px solid #fecdd3; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(225,29,72,0.1); transition: all 0.2s;" onmouseover="if(!window.isRecordingVoice) { this.style.transform='scale(1.05)'; this.style.background='#ffe4e6' }" onmouseout="if(!window.isRecordingVoice) { this.style.transform='scale(1)'; this.style.background='#fff1f2' }">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </button>
                <button type="button" onclick="sendMessage()" style="background: linear-gradient(135deg, #fb7185 0%, #fdba74 100%); color: white; width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; border-radius: 20px; border: none; cursor: pointer; box-shadow: 0 4px 14px 0 rgba(251,113,133,0.39); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(251,113,133,0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px 0 rgba(251,113,133,0.39)'">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.25rem; width: 1.25rem; transform: translateX(2px);" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        window.conversationId = {{ $conversation->id }};
        window.otherParticipantId = {{ $otherParticipantId }};
        window.otherParticipantType = '{{ $otherTypeShort }}';
        window.userId = {{ $participantId ?? 0 }};
        window.userType = '{{ $participantType }}';
        window.userTypeShort = '{{ $participantTypeShort }}';
    </script>

    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
    <script>
        window.axios = axios;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        window.axios.defaults.withCredentials = true;
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }
    </script>

    <style>
        #chat-box {
            background: radial-gradient(ellipse at top right, rgba(255,237,213,0.5), #ffffff, rgba(255,228,230,0.3));
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.99); }
        }
        .message-row {
            display: flex;
            width: 100%;
            margin-bottom: 20px;
            animation: fadeInUp 0.3s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message-row.my-message {
            justify-content: flex-end;
        }
        .message-row.their-message {
            justify-content: flex-start;
        }
        .message-container {
            max-width: 70%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .message-row.my-message .message-container {
            align-items: flex-end;
        }
        .message-row.their-message .message-container {
            align-items: flex-start;
        }
        .sender-name {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fb7185;
            font-weight: 700;
            margin-bottom: 6px;
            margin-left: 12px;
        }
        .message-bubble {
            padding: 14px 18px;
            border-radius: 24px;
            word-wrap: break-word;
            font-size: 14.5px;
            line-height: 1.6;
            transition: transform 0.3s ease;
        }
        .message-row:hover .message-bubble {
            transform: translateY(-2px);
        }
        
        /* User's message - Sunset theme */
        .my-message .message-bubble {
            background: linear-gradient(135deg, #f43f5e 0%, #fb923c 100%);
            color: white;
            border-bottom-right-radius: 6px;
            box-shadow: 0 4px 14px 0 rgba(251, 113, 133, 0.39);
        }

        /* Admin/Other message - Clean white style */
        .their-message .message-bubble {
            background: #ffffff;
            color: #44403c;
            border: 1px solid #fff7ed;
            border-bottom-left-radius: 6px;
            box-shadow: 0 4px 20px -4px rgba(251, 146, 60, 0.08);
        }
        
        .message-time {
            font-size: 11px;
            color: #fda4af;
            margin-top: 6px;
            padding: 0 4px;
            font-weight: 500;
        }
        .my-message .message-time {
            color: #fda4af;
        }
        @keyframes vcPulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
        }
    </style>


    <script>
        window.loadedMessageIds = new Set();
        window.lastMessageId = 0;
        window.lastMarkedReadAt = 0;
        window.lastLoadTime = 0;
        window.loadDebounceMs = 2000;

        function isImage(url) {
            if (!url) return false;
            const ext = url.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
        }

        function syncMessageCounter(reason = 'refresh') {
            try {
                const payload = JSON.stringify({
                    reason,
                    conversationId: window.conversationId,
                    at: Date.now(),
                });

                localStorage.setItem('message-counter-sync', payload);
                window.dispatchEvent(new CustomEvent('message-counter-sync', {
                    detail: JSON.parse(payload)
                }));
            } catch (error) {
                console.error('Message counter sync error:', error);
            }
        }

        window.downloadFile = function(url, filename) {
            if (!url) return;
            
            // Extract relative path from absolute URL
            const match = url.match(/uploads\/messages\/(.+)$/);
            if (match) {
                const relativePath = 'uploads/messages/' + match[1].split('?')[0];
                const downloadUrl = `/messages/download-attachment?path=${encodeURIComponent(relativePath)}&name=${encodeURIComponent(filename || 'file')}`;
                
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                setTimeout(() => document.body.removeChild(link), 100);
                return;
            }

            // Fallback for external or non-standard URLs
            fetch(url).then(r => r.blob()).then(blob => {
                const blobUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename || 'download';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(blobUrl);
            }).catch(err => {
                console.error('Download fallback failed:', err);
                window.location.href = url;
            });
        };

        async function markConversationAsRead(force = false) {
            const now = Date.now();

            if (!force && now - window.lastMarkedReadAt < 1500) {
                return;
            }

            try {
                const response = await axios.post('/mark-read', {
                    conversation_id: window.conversationId
                });

                window.lastMarkedReadAt = now;
                console.log('Marked conversation as read:', response.data);
                syncMessageCounter('read');
            } catch (error) {
                console.error('Mark read error:', error);
            }
        }

        function scrollToBottom() {
            const chatBox = document.getElementById('chat-box');
            if (!chatBox) return;
            
            const performScroll = (behavior = 'smooth') => {
                chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: behavior });
            };

            // Instant snap
            chatBox.scrollTop = chatBox.scrollHeight;
            
            // Smooth adjustment after render
            requestAnimationFrame(() => performScroll('smooth'));

            // Handle images
            chatBox.querySelectorAll('img').forEach(img => {
                if (!img.complete) img.addEventListener('load', () => performScroll('smooth'), { once: true });
            });

            // Follow-up checks
            setTimeout(() => performScroll('smooth'), 150);
            setTimeout(() => performScroll('smooth'), 500);
        }

        window.appendMessage = function(message) {
            let chatBox = document.getElementById('chat-box');
            if (!chatBox) return;

            let messageTypeShort = message.sender_type ? message.sender_type.split('\\').pop().toLowerCase() : '';
            let isMe = (message.sender_id == window.userId && messageTypeShort === window.userTypeShort);

            let time = message.created_at ? new Date(message.created_at).toLocaleTimeString() : new Date().toLocaleTimeString();
            let senderName = message.sender_name || (isMe ? 'You' : 'Unknown');

            let row = document.createElement('div');
            row.className = isMe ? 'message-row my-message' : 'message-row their-message';

            let container = document.createElement('div');
            container.className = 'message-container';

            let fileUrl = message.file_url || message.fileUrl || '';
            let hasImage = fileUrl && (fileUrl.endsWith('.jpg') || fileUrl.endsWith('.jpeg') || fileUrl.endsWith('.png') || fileUrl.endsWith('.gif') || fileUrl.endsWith('.webp'));
            let isAudio = fileUrl && (fileUrl.endsWith('.webm') || fileUrl.endsWith('.mp3') || fileUrl.endsWith('.wav') || fileUrl.endsWith('.ogg') || fileUrl.endsWith('.m4a') || fileUrl.endsWith('.aac'));

            if (isMe) {
                let content = message.body || '';
                if (hasImage) {
                    content += `<div style="position: relative; margin-top: 8px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <img src="${fileUrl}" style="max-width: 100%; display: block; border-radius: 12px;">
                    </div>`;
                } else if (isAudio) {
                    content += `
                    <div style="margin-top: 8px; padding: 10px; background: rgba(255,255,255,0.15); border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);">
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; color: white;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <span style="color: white; font-size: 11px; font-weight: bold; text-transform: uppercase;">Voice Message</span>
                        </div>
                        <audio controls src="${fileUrl}" style="height: 38px; max-width: 240px; width: 100%; border-radius: 20px;"></audio>
                    </div>`;
                } else if (fileUrl) {
                    let fileName = fileUrl.split('/').pop().split('?')[0] || 'File';
                    content += `<a href="${fileUrl}" target="_blank" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; margin-top: 8px; background: #fff; border: 1px solid rgba(255,255,255,0.3); border-radius: 14px; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.08);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'">
                        <div style="width: 32px; height: 32px; background: #fff1f2; border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; color: #f43f5e;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="color: #e11d48; font-size: 13px; font-weight: 700; line-height: 1.2;">${fileName}</span>
                            <span style="color: #9f1239; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; opacity: 0.8;">View Document</span>
                        </div>
                    </a>`;
                }
                container.innerHTML = `
                    <div class="message-bubble">${content}</div>
                    <div class="message-time">${time} ✓</div>`;
            } else {
                let content = message.body || '';
                if (hasImage) {
                    content += `<div style="position: relative; margin-top: 8px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
                        <img src="${fileUrl}" style="max-width: 100%; display: block; border-radius: 12px;">
                        <a href="javascript:void(0)" onclick="downloadFile('${fileUrl}', '${fileName}')" style="position: absolute; bottom: 8px; right: 8px; display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(8px); border: 1px solid rgba(0,0,0,0.05); border-radius: 10px; text-decoration: none; font-size: 12px; color: #1e293b; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.95)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255,255,255,0.8)'; this.style.transform='scale(1)'">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; color: #f43f5e;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </a>
                    </div>`;
                } else if (isAudio) {
                    content += `
                    <div style="margin-top: 8px; padding: 10px; background: #f1f5f9; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; color: #64748b;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <span style="color: #64748b; font-size: 11px; font-weight: bold; text-transform: uppercase;">Voice Message</span>
                        </div>
                        <audio controls src="${fileUrl}" style="height: 38px; max-width: 240px; width: 100%; border-radius: 20px;"></audio>
                    </div>`;
                } else if (fileUrl) {
                    let fileName = fileUrl.split('/').pop().split('?')[0] || 'File';
                    content += `<div style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px;">
                        <a href="${fileUrl}" target="_blank" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#f8fafc'; this.style.transform='translateY(0)'">
                            <div style="width: 32px; height: 32px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; color: #f43f5e;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="color: #1e293b; font-size: 13px; font-weight: 600; line-height: 1.2;">${fileName}</span>
                                <span style="color: #64748b; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.02em;">View File</span>
                            </div>
                        </a>
                        <a href="javascript:void(0)" onclick="downloadFile('${fileUrl}', '${fileName}')" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; text-decoration: none; font-size: 11px; color: #e11d48; font-weight: 800; transition: all 0.2s;" onmouseover="this.style.background='#ffe4e6'" onmouseout="this.style.background='#fff1f2'">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            DOWNLOAD FILE
                        </a>
                    </div>`;
                }
                container.innerHTML = `
                    <div class="sender-name">${senderName}</div>
                    <div class="message-bubble">${content}</div>
                    <div class="message-time">${time}</div>`;
            }

            row.appendChild(container);
            chatBox.appendChild(row);
            scrollToBottom(isMe);
        };

        window.isLoadingMessages = false;

        async function loadMessages() {
            if (window.isLoadingMessages) return;
            const now = Date.now();
            if (now - window.lastLoadTime < window.loadDebounceMs) return;
            window.lastLoadTime = now;
            window.isLoadingMessages = true;

            const chatSkeleton = document.getElementById('chat-skeleton');
            const emptyState = document.getElementById('empty-state');
            const chatBox = document.getElementById('chat-box');

            if (window.loadedMessageIds.size === 0) {
                if (chatSkeleton) chatSkeleton.style.display = 'block';
                if (emptyState) emptyState.style.display = 'none';
            }

            try {
                const response = await axios.get('/messages/' + window.conversationId + '?t=' + now);
                console.log('Load messages response:', response.data);
                const messages = response.data.messages || [];
                messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                
                const isFirstLoad = window.loadedMessageIds.size === 0;
                
                if (isFirstLoad && messages.length > 0) {
                    // Smooth transition: hide skeleton after a tiny delay to ensure feel
                    setTimeout(() => {
                        if (chatSkeleton) chatSkeleton.style.display = 'none';
                        messages.forEach(msg => {
                            if (!window.loadedMessageIds.has(msg.id)) {
                                window.loadedMessageIds.add(msg.id);
                                window.appendMessage(msg);
                            }
                        });
                    }, 300);
                } else {
                    if (chatSkeleton) chatSkeleton.style.display = 'none';
                    if (messages.length === 0) {
                        if (emptyState) emptyState.style.display = 'block';
                    } else {
                        if (emptyState) emptyState.style.display = 'none';
                        messages.forEach(msg => {
                            if (!window.loadedMessageIds.has(msg.id)) {
                                window.loadedMessageIds.add(msg.id);
                                window.appendMessage(msg);
                            }
                        });
                    }
                }

                if (window.loadedMessageIds.size > 0) {
                    window.lastMessageId = Math.max(...Array.from(window.loadedMessageIds));
                }
                await markConversationAsRead(true);
                console.log('Loaded messages. Total loaded:', window.loadedMessageIds.size, 'Last ID:', window.lastMessageId);
            } catch (error) {
                console.error('Load error:', error);
                if (chatSkeleton) chatSkeleton.style.display = 'none';
            } finally {
                window.isLoadingMessages = false;
            }
        }

        window.sendMessage = async function() {
            let input = document.getElementById('message-input');
            let message = input.value.trim();
            let fileInput = document.getElementById('file-input');
            let file = fileInput.files[0];
            
            if (!message && !file) return;
            
            input.disabled = true;
            let formData = new FormData();
            if (message) formData.append('message', message);
            if (file) formData.append('file', file);
            formData.append('conversation_id', window.conversationId);
            
            console.log('Sending message:', { message, hasFile: !!file, conversationId: window.conversationId });
            
            try {
                const response = await axios.post('/send-message', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                console.log('Send response:', response.data);
                input.value = '';
                fileInput.value = '';
                document.getElementById('file-preview').style.display = 'none';
                if (response.data && response.data.id) {
                    window.loadedMessageIds.add(response.data.id);
                    window.lastMessageId = Math.max(window.lastMessageId, response.data.id);
                    window.appendMessage(response.data);
                    syncMessageCounter('sent');
                }
            } catch (error) {
                console.error('Send error:', error);
                alert('Failed to send message');
            } finally {
                input.disabled = false;
                input.focus();
            }
        };

        function isImageFile(file) {
            if (!file) return false;
            const ext = file.name.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
        }

        window.handleFileSelect = function(event) {
            let file = event.target.files[0];
            if (file) {
                const preview = document.getElementById('file-preview');
                const nameEl = document.getElementById('file-name');
                nameEl.innerHTML = '';
                
                if (isImageFile(file)) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.style.borderRadius = '8px';
                    nameEl.appendChild(img);
                } else {
                    nameEl.textContent = file.name;
                }
                preview.style.display = 'block';
            }
        };

        window.removeFile = function() {
            document.getElementById('file-input').value = '';
            document.getElementById('file-preview').style.display = 'none';
        };

        loadMessages();

        // Poll every 3 seconds with debounce
        let pollInterval;
        const startPolling = () => {
            pollInterval = setInterval(async () => {
                if (window.isLoadingMessages) return;
                const now = Date.now();
                if (now - window.lastLoadTime < window.loadDebounceMs) return;
                window.lastLoadTime = now;
                window.isLoadingMessages = true;
                try {
                const response = await axios.get('/messages/' + window.conversationId + '?t=' + Date.now());
                console.log('Poll response:', response.data);
                const messages = response.data.messages || [];
                if (messages.length > 0) {
                    messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    const newMessageId = messages[messages.length - 1].id;
                    console.log('Last message ID:', newMessageId, 'Prev last:', window.lastMessageId);
                    if (newMessageId > window.lastMessageId) {
                        messages.forEach(msg => {
                            if (!window.loadedMessageIds.has(msg.id)) {
                                window.loadedMessageIds.add(msg.id);
                                let senderTypeShort = msg.sender_type ? msg.sender_type.split('\\').pop().toLowerCase() : '';
                                // Check if sender is SAME person (both id AND type must match)
                                let isSameUser = (msg.sender_id == window.userId && senderTypeShort === window.userTypeShort);
                                console.log('Message', msg.id, 'from:', senderTypeShort, 'to:', window.userTypeShort, 'sameUser:', isSameUser);
                                if (!isSameUser) {
                                    window.appendMessage(msg);
                                }
                            }
                        });
                        window.lastMessageId = newMessageId;
                        await markConversationAsRead();
                    }
                }
            } catch (e) {
                console.error('Poll error:', e);
            } finally {
                window.isLoadingMessages = false;
            }
        }, 3000);
        };
        startPolling();

        const input = document.getElementById('message-input');
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    window.sendMessage();
                }
            });
        }

        const otherParticipantId = window.otherParticipantId;
        const otherParticipantType = window.otherParticipantType;

        async function checkOnlineStatus() {
            if (!otherParticipantId || !otherParticipantType) return;
            try {
                const response = await axios.get(`/online-status/${otherParticipantId}/${otherParticipantType}`);
                const isOnline = response.data.online;
                const dot = document.getElementById('online-dot');
                const text = document.getElementById('online-text');
                if (dot && text) {
                    if (isOnline) {
                        dot.style.background = '#10b981';
                        dot.style.boxShadow = '0 0 10px #10b981';
                        text.textContent = 'Online';
                    } else {
                        dot.style.background = '#64748b';
                        dot.style.boxShadow = '0 0 8px rgba(100, 116, 139, 0.5)';
                        text.textContent = 'Offline';
                    }
                }
            } catch (error) {}
        }

        async function sendHeartbeat() {
            try {
                await axios.post('/online-heartbeat');
            } catch (error) {}
        }

        checkOnlineStatus();
        setInterval(checkOnlineStatus, 5000);
        setInterval(sendHeartbeat, 8000);
        window.addEventListener('focus', () => markConversationAsRead(true));
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                markConversationAsRead(true);
            }
        });


        window.isRecordingVoice = false;
        window.voiceMediaRecorder = null;
        window.voiceAudioChunks = [];

        window.toggleVoiceRecording = async function() {
            const btn = document.getElementById('voice-record-btn');
            
            if (window.isRecordingVoice) {
                if(window.voiceMediaRecorder) {
                    window.voiceMediaRecorder.stop();
                }
                window.isRecordingVoice = false;
                btn.style.background = '#fff1f2';
                btn.style.color = '#e11d48';
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>`;
                return;
            }
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                window.voiceMediaRecorder = new MediaRecorder(stream);
                window.voiceAudioChunks = [];
                
                window.voiceMediaRecorder.ondataavailable = e => {
                    if (e.data.size > 0) window.voiceAudioChunks.push(e.data);
                };
                
                window.voiceMediaRecorder.onstop = () => {
                    stream.getTracks().forEach(track => track.stop());
                    if (window.voiceAudioChunks.length === 0) return;
                    
                    const audioBlob = new Blob(window.voiceAudioChunks, { type: 'audio/webm' });
                    const file = new File([audioBlob], `voice_${Date.now()}.webm`, { type: 'audio/webm' });
                    
                    window.sendAudioFile(file);
                };
                
                window.voiceMediaRecorder.start();
                window.isRecordingVoice = true;
                btn.style.background = '#fee2e2';
                btn.style.color = '#dc2626';
                btn.innerHTML = `<span style="display:block; width:14px; height:14px; background:#dc2626; border-radius:4px; animation: vcPulse 1s infinite;"></span>`;
            } catch (err) {
                alert('Microphone access is required to send voice messages.');
                console.error(err);
            }
        };

        window.sendAudioFile = async function(file) {
            let formData = new FormData();
            formData.append('file', file);
            formData.append('conversation_id', window.conversationId);
            
            try {
                const response = await window.axios.post('/send-message', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                if (response.data && response.data.id) {
                    window.loadedMessageIds.add(response.data.id);
                    window.lastMessageId = Math.max(window.lastMessageId, response.data.id);
                    window.appendMessage(response.data);
                    if (typeof window.syncMessageCounter === 'function') window.syncMessageCounter('sent');
                }
            } catch (error) {
                console.error('Record send error:', error);
            }
        };

        // Ensure messages auto scroll

        // Ensure only one audio plays at a time
        document.addEventListener('play', (event) => {
            const audios = document.getElementsByTagName('audio');
            for (let i = 0, len = audios.length; i < len; i++) {
                if (audios[i] != event.target) {
                    audios[i].pause();
                }
            }
        }, true);
    </script>


</x-messaging::layouts.master>
