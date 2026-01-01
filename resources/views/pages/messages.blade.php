<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Messages') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">Message Center</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Review member and trainer inquiries, then respond directly from the admin panel.
                    </p>
                    <div id="messages-status" class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        Loading conversations...
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold">Conversations</h4>
                            <span class="text-xs uppercase tracking-wide text-gray-400">Inbox</span>
                        </div>
                        <div id="conversations-list" class="space-y-3">
                            <div class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                                No conversations loaded yet.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 id="thread-title" class="text-lg font-semibold">Conversation</h4>
                                <p id="thread-subtitle" class="text-sm text-gray-600 dark:text-gray-300">
                                    Select a conversation to reply to a member or trainer.
                                </p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900 dark:text-gray-300">Admin</span>
                        </div>

                        <div id="thread-messages" class="space-y-4">
                            <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                No conversation selected.
                            </div>
                        </div>

                        <form id="reply-form" class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium" for="message-reply">Reply</label>
                                <textarea id="message-reply" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Write a reply to the member or trainer..." disabled></textarea>
                            </div>
                            <div class="flex flex-col gap-2 text-xs text-gray-500 dark:text-gray-400 sm:flex-row sm:items-center sm:justify-between">
                                <p id="reply-helper">Replies will be sent from the admin account.</p>
                                <button type="submit" id="reply-submit" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" disabled>
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const statusBox = document.getElementById('messages-status');
        const conversationsList = document.getElementById('conversations-list');
        const threadMessages = document.getElementById('thread-messages');
        const threadTitle = document.getElementById('thread-title');
        const threadSubtitle = document.getElementById('thread-subtitle');
        const replyForm = document.getElementById('reply-form');
        const replyTextarea = document.getElementById('message-reply');
        const replySubmit = document.getElementById('reply-submit');
        const replyHelper = document.getElementById('reply-helper');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let activeUserId = null;
        let conversationsCache = [];

        const setStatus = (message, type = 'info') => {
            const base = 'rounded-md px-4 py-3 text-sm ';
            const styles = {
                info: 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200',
                success: 'bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-100',
                error: 'bg-rose-50 dark:bg-rose-900 text-rose-700 dark:text-rose-100',
            };
            statusBox.className = base + (styles[type] || styles.info);
            statusBox.textContent = message;
        };

        const apiFetch = async (url, options = {}) => {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers || {}),
                },
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = data.message || 'Request failed.';
                setStatus(message, 'error');
                throw new Error(message);
            }
            return data;
        };

        const formatDate = (value) => {
            if (!value) return '';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString();
        };

        const setReplyState = (enabled) => {
            replyTextarea.disabled = !enabled;
            replySubmit.disabled = !enabled;
            replyHelper.textContent = enabled
                ? 'Replies will be sent from the admin account.'
                : 'Select a conversation to enable replies.';
        };

        const renderConversations = (conversations) => {
            if (!conversations.length) {
                conversationsList.innerHTML = `
                    <div class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                        No messages yet. Conversations from members and trainers will appear here.
                    </div>
                `;
                return;
            }

            conversationsList.innerHTML = conversations.map((conversation) => {
                const active = conversation.user_id === activeUserId;
                const roleColor = conversation.user_role === 'trainer'
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200'
                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200';
                const activeClasses = active
                    ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                    : 'border-gray-200 dark:border-gray-700';

                return `
                    <button
                        type="button"
                        data-user-id="${conversation.user_id}"
                        class="conversation-button w-full rounded-md border px-4 py-3 text-left hover:border-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 ${activeClasses}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold">${conversation.user_name}</p>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold ${roleColor}">${conversation.user_role}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">${conversation.preview}</p>
                        <p class="mt-1 text-xs text-gray-400">${formatDate(conversation.updated_at)}</p>
                    </button>
                `;
            }).join('');
        };

        const renderThread = (thread) => {
            if (!thread.length) {
                threadMessages.innerHTML = `
                    <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        No messages in this conversation yet.
                    </div>
                `;
                return;
            }

            threadMessages.innerHTML = thread.map((message) => {
                const isAdmin = message.is_admin;
                const baseClasses = isAdmin
                    ? 'ml-auto bg-emerald-50 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-100'
                    : 'bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-200';
                const author = isAdmin ? 'Admin' : message.sender_name;

                return `
                    <div class="max-w-xl rounded-lg px-4 py-3 text-sm shadow-sm ${baseClasses}">
                        <p class="font-semibold">${author}</p>
                        <p class="mt-1">${message.body}</p>
                        <p class="mt-2 text-xs ${isAdmin ? 'text-emerald-500 dark:text-emerald-200' : 'text-gray-400'}">${formatDate(message.created_at)}</p>
                    </div>
                `;
            }).join('');
        };

        const loadConversations = async () => {
            setStatus('Loading conversations...');
            const data = await apiFetch('/admin/messages');
            conversationsCache = data.conversations || [];
            renderConversations(conversationsCache);
            setStatus('Conversations updated.', 'success');
        };

        const loadThread = async (userId) => {
            if (!userId) return;
            const data = await apiFetch(`/admin/messages/${userId}`);
            threadTitle.textContent = data.user.name;
            threadSubtitle.textContent = `${data.user.role} · ${data.user.email}`;
            renderThread(data.messages || []);
            setReplyState(true);
        };

        conversationsList.addEventListener('click', (event) => {
            const button = event.target.closest('.conversation-button');
            if (!button) return;
            activeUserId = Number(button.dataset.userId);
            renderConversations(conversationsCache);
            loadThread(activeUserId).catch(() => {});
        });

        replyForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!activeUserId) return;
            const body = replyTextarea.value.trim();
            if (!body) {
                setStatus('Please enter a reply before sending.', 'error');
                return;
            }

            replySubmit.disabled = true;
            await apiFetch(`/admin/messages/${activeUserId}`, {
                method: 'POST',
                body: JSON.stringify({ body }),
            });
            replyTextarea.value = '';
            setStatus('Reply sent.', 'success');
            await loadThread(activeUserId);
            await loadConversations();
            replySubmit.disabled = false;
        });

        loadConversations().catch(() => {
            setStatus('Unable to load conversations right now.', 'error');
        });
        setReplyState(false);
    </script>
</x-app-layout>
