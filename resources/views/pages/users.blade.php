<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div>
                        <h3 class="text-lg font-semibold">User Management</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Manage your users from this dashboard.
                        </p>
                    </div>

                    <div class="rounded-md bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm text-gray-700 dark:text-gray-200" id="users-message">
                        Users are loaded automatically.
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">Users List</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">ID</th>
                                    <th class="px-4 py-2 text-left font-semibold">Name</th>
                                    <th class="px-4 py-2 text-left font-semibold">Email</th>
                                    <th class="px-4 py-2 text-left font-semibold">Phone</th>
                                    <th class="px-4 py-2 text-left font-semibold">Role</th>
                                    <th class="px-4 py-2 text-left font-semibold">Status</th>
                                    <th class="px-4 py-2 text-left font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td colspan="7" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No users loaded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Click edit on a user to update their details.
                        </p>
                        <button
                            type="button"
                            id="toggle-create-user"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            Create User
                        </button>
                    </div>
                </div>
            </div>

            <div id="create-user-panel" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Create User</h3>
                        <button type="button" id="close-create-user" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-200">
                            Close
                        </button>
                    </div>
                    <form id="create-user-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium" for="create-name">Name</label>
                            <input id="create-name" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="create-email">Email</label>
                            <input id="create-email" type="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="create-phone">Phone</label>
                            <input id="create-phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="create-password">Password</label>
                            <input id="create-password" type="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="create-card-id">Card ID (optional)</label>
                            <input id="create-card-id" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="create-role">Role</label>
                            <select id="create-role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="administrator">administrator</option>
                                <option value="trainer">trainer</option>
                                <option value="user">user</option>
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Create User
                        </button>
                    </form>
                </div>
            </div>

            <div id="edit-user-panel" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Edit User</h3>
                        <button type="button" id="close-edit-user" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-200">
                            Close
                        </button>
                    </div>
                    <form id="update-user-form" class="space-y-4">
                        <input type="hidden" id="update-user-id">
                        <div>
                            <label class="block text-sm font-medium" for="update-name">Name</label>
                            <input id="update-name" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="update-email">Email</label>
                            <input id="update-email" type="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="update-phone">Phone</label>
                            <input id="update-phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="update-password">Password</label>
                            <input id="update-password" type="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Leave blank to keep current">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="update-role">Role</label>
                            <select id="update-role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Select role</option>
                                <option value="administrator">administrator</option>
                                <option value="trainer">trainer</option>
                                <option value="user">user</option>
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const messageBox = document.getElementById('users-message');
        const usersTable = document.getElementById('users-table');
        const updateUserId = document.getElementById('update-user-id');
        const updateName = document.getElementById('update-name');
        const updateEmail = document.getElementById('update-email');
        const updatePhone = document.getElementById('update-phone');
        const updatePassword = document.getElementById('update-password');
        const updateRole = document.getElementById('update-role');
        const createPanel = document.getElementById('create-user-panel');
        const editPanel = document.getElementById('edit-user-panel');

        const setMessage = (message, type = 'info') => {
            const base = 'rounded-md px-4 py-3 text-sm ';
            const styles = {
                info: 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200',
                success: 'bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-100',
                error: 'bg-rose-50 dark:bg-rose-900 text-rose-700 dark:text-rose-100',
            };
            messageBox.className = base + (styles[type] || styles.info);
            messageBox.textContent = message;
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                setMessage(message, 'error');
                throw new Error(message);
            }
            return data;
        };

        const openPanel = (panel) => {
            panel.classList.remove('hidden');
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        const closePanel = (panel) => {
            panel.classList.add('hidden');
        };

        const renderUsers = (users) => {
            if (!users.length) {
                usersTable.innerHTML = '<tr><td colspan="7" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No users found.</td></tr>';
                return;
            }

            usersTable.innerHTML = users.map((user) => {
                const status = user.deleted_at ? 'Deleted' : 'Active';
                const actions = user.deleted_at
                    ? `<div class="flex gap-2">
                        <button data-id="${user.id}" class="restore-user inline-flex items-center px-3 py-1 bg-emerald-600 text-white rounded-md text-xs">Restore</button>
                        <button data-id="${user.id}" class="force-delete-user inline-flex items-center px-3 py-1 bg-black text-dark rounded-md text-xs">Delete</button>
                    </div>`
                    : `<div class="flex gap-2">
                        <button data-id="${user.id}" data-phone="${user.phone || ''}" class="edit-user inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-md text-xs">Edit</button>
                        <button data-id="${user.id}" class="soft-delete-user inline-flex items-center px-3 py-1 bg-rose-600 text-white rounded-md text-xs">Soft Delete</button>
                    </div>`;

                return `
                    <tr>
                        <td class="px-4 py-2">${user.id}</td>
                        <td class="px-4 py-2">${user.name}</td>
                        <td class="px-4 py-2">${user.email}</td>
                        <td class="px-4 py-2">${user.phone || '-'}</td>
                        <td class="px-4 py-2">${user.role}</td>
                        <td class="px-4 py-2">${status}</td>
                        <td class="px-4 py-2">${actions}</td>
                    </tr>
                `;
            }).join('');

            usersTable.querySelectorAll('.soft-delete-user').forEach((button) => {
                button.addEventListener('click', async () => {
                    const userId = button.dataset.id;
                    if (!confirm('Soft delete this user?')) return;
                    await apiFetch(`/admin/users/${userId}`, { method: 'DELETE' });
                    setMessage('User soft deleted successfully.', 'success');
                    loadUsers();
                });
            });

            usersTable.querySelectorAll('.force-delete-user').forEach((button) => {
                button.addEventListener('click', async () => {
                    const userId = button.dataset.id;
                    if (!confirm('Permanently delete this user? This action cannot be undone.')) return;
                    await apiFetch(`/admin/users/${userId}/force`, { method: 'DELETE' });
                    setMessage('User permanently deleted successfully.', 'success');
                    loadUsers();
                 });
            });

            usersTable.querySelectorAll('.restore-user').forEach((button) => {
                button.addEventListener('click', async () => {
                    const userId = button.dataset.id;
                    await apiFetch(`/admin/users/${userId}/restore`, { method: 'POST' });
                    setMessage('User restored successfully.', 'success');
                    loadUsers();
                });
            });

            usersTable.querySelectorAll('.edit-user').forEach((button) => {
                button.addEventListener('click', () => {
                    const row = button.closest('tr');
                    updateUserId.value = button.dataset.id;
                    updateName.value = row.children[1].textContent.trim();
                    updateEmail.value = row.children[2].textContent.trim();
                    updatePhone.value = button.dataset.phone || '';
                    updateRole.value = row.children[4].textContent.trim();
                    updatePassword.value = '';
                    openPanel(editPanel);
                });
            });
        };

            const loadUsers = async () => {
            const data = await apiFetch('/admin/users', { method: 'GET' });
            renderUsers(data.users || []);
            setMessage(data.message || 'Users loaded.', 'success');
        };

        document.getElementById('toggle-create-user').addEventListener('click', () => openPanel(createPanel));
        document.getElementById('close-create-user').addEventListener('click', () => closePanel(createPanel));
        document.getElementById('close-edit-user').addEventListener('click', () => closePanel(editPanel));

        document.getElementById('create-user-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const cardId = document.getElementById('create-card-id').value.trim();
            const payload = {
                name: document.getElementById('create-name').value.trim(),
                email: document.getElementById('create-email').value.trim(),
                phone: document.getElementById('create-phone').value.trim(),
                password: document.getElementById('create-password').value,
                role: document.getElementById('create-role').value,
            };

            if (cardId) {
                payload.card_id = cardId;
            }

            await apiFetch('/admin/users', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            setMessage('User created successfully.', 'success');
            event.target.reset();
            closePanel(createPanel);
            loadUsers();
        });

        document.getElementById('update-user-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const userId = updateUserId.value;
            if (!userId) {
                setMessage('Select a user to update.', 'error');
                return;
            }

            const payload = {};
            const name = updateName.value.trim();
            const email = updateEmail.value.trim();
            const phone = updatePhone.value.trim();
            const password = updatePassword.value;
            const role = updateRole.value;

            if (name) payload.name = name;
            if (email) payload.email = email;
            if (phone) payload.phone = phone;
            if (password) payload.password = password;
            if (role) payload.role = role;

            if (!Object.keys(payload).length) {
                setMessage('Provide at least one field to update.', 'error');
                return;
            }

            await apiFetch(`/admin/users/${userId}`, {
                method: 'PATCH',
                body: JSON.stringify(payload),
            });

            setMessage('User updated successfully.', 'success');
            closePanel(editPanel);
            updatePassword.value = '';
            loadUsers();
        });

        loadUsers();
    </script>
</x-app-layout>
