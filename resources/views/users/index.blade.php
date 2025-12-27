<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-black dark:text-white">Users</h2>
                <p class="text-sm text-black/50 dark:text-white/50">Manage members, trainers and admins.</p>
            </div>

            <button
                class="inline-flex items-center gap-2 rounded-2xl bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-black/90"
                @click="openCreate()">
                <span class="text-lg leading-none">+</span> Create User
            </button>
        </div>
    </x-slot>

    <div class="py-8" x-data="usersPage()" x-init="loadUsers()">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            <template x-if="message">
                <div class="rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black">
                    <span class="font-semibold">Success:</span> <span x-text="message"></span>
                </div>
            </template>
            <template x-if="error">
                <div class="rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black">
                    <span class="font-semibold">Error:</span> <span x-text="error"></span>
                </div>
            </template>

            <!-- Toolbar -->
            <div class="rounded-3xl border border-black/10 bg-white p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="text-sm font-semibold text-black">Users List</div>
                        <div class="text-xs text-black/50" x-text="`(${filteredUsers().length} shown)`"></div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            class="w-full sm:w-72 rounded-2xl border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black/20"
                            placeholder="Search name or email..."
                            x-model="search"
                        />
                        <button
                            class="rounded-2xl border border-black px-3 py-2 text-sm font-semibold text-black hover:bg-black hover:text-white transition"
                            @click="loadUsers()">
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-black/50">
                        <tr>
                            <th class="py-3 pr-4">Name</th>
                            <th class="py-3 pr-4">Email</th>
                            <th class="py-3 pr-4">Role</th>
                            <th class="py-3 pr-4">Created</th>
                            <th class="py-3">Actions</th>
                        </tr>
                        </thead>

                        <tbody class="text-black">
                        <template x-for="u in filteredUsers()" :key="u.id">
                            <tr class="border-t border-black/10">
                                <td class="py-3 pr-4 font-medium" x-text="u.name"></td>
                                <td class="py-3 pr-4 text-black/70" x-text="u.email"></td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-full border border-black/10 px-2 py-1 text-xs font-semibold"
                                          x-text="u.role"></span>
                                </td>
                                <td class="py-3 pr-4 text-black/60" x-text="formatDate(u.created_at)"></td>
                                <td class="py-3 flex gap-2">
                                    <button class="rounded-xl border border-black/10 px-3 py-1.5 text-xs font-semibold hover:border-black"
                                            @click="openEdit(u)">Edit</button>
                                    <button class="rounded-xl border border-black px-3 py-1.5 text-xs font-semibold text-black hover:bg-black hover:text-white transition"
                                            @click="deleteUser(u.id)">Delete</button>
                                </td>
                            </tr>
                        </template>

                        <template x-if="filteredUsers().length === 0">
                            <tr class="border-t border-black/10">
                                <td colspan="5" class="py-8 text-center text-black/50">
                                    No users found.
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal -->
            <div x-show="modal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

                <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 border border-black/10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold" x-text="modal.mode === 'create' ? 'Create User' : 'Edit User'"></h3>
                            <p class="text-sm text-black/50">Black & white clean admin UI</p>
                        </div>
                        <button class="text-black/60 hover:text-black" @click="closeModal()">✕</button>
                    </div>

                    <form class="mt-5 grid gap-4 sm:grid-cols-2" @submit.prevent="submitModal()">
                        <div>
                            <label class="text-xs font-semibold text-black/60">Name</label>
                            <input class="mt-1 w-full rounded-2xl border border-black/10 px-3 py-2 text-sm"
                                   x-model="modal.form.name" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-black/60">Email</label>
                            <input type="email" class="mt-1 w-full rounded-2xl border border-black/10 px-3 py-2 text-sm"
                                   x-model="modal.form.email" required>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-black/60">Role</label>
                            <select class="mt-1 w-full rounded-2xl border border-black/10 px-3 py-2 text-sm"
                                    x-model="modal.form.role" required>
                                <option value="trainer">Trainer</option>
                                <option value="user">User</option>
                                <option value="administrator">Administrator</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-black/60">Password</label>
                            <input type="password" class="mt-1 w-full rounded-2xl border border-black/10 px-3 py-2 text-sm"
                                   x-model="modal.form.password" :required="modal.mode==='create'">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-black/60">Confirm Password</label>
                            <input type="password" class="mt-1 w-full rounded-2xl border border-black/10 px-3 py-2 text-sm"
                                   x-model="modal.form.password_confirmation" :required="modal.mode==='create'">
                        </div>

                        <div class="sm:col-span-2 flex gap-2 justify-end pt-2">
                            <button type="button"
                                    class="rounded-2xl border border-black/10 px-4 py-2 text-sm font-semibold hover:border-black"
                                    @click="closeModal()">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="rounded-2xl bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-black/90">
                                <span x-text="modal.mode==='create' ? 'Create' : 'Save'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <script>
            function usersPage() {
                return {
                    users: [],
                    search: '',
                    message: '',
                    error: '',

                    modal: {
                        open: false,
                        mode: 'create', // create | edit
                        id: null,
                        form: { name:'', email:'', role:'trainer', password:'', password_confirmation:'' }
                    },

                    csrfToken() {
                        const el = document.querySelector('meta[name="csrf-token"]');
                        return el ? el.getAttribute('content') : '';
                    },

                    async request(url, options = {}) {
                        this.message = '';
                        this.error = '';
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                                ...(options.headers || {}),
                            },
                            credentials: 'same-origin',
                            ...options,
                        });

                        const text = await res.text();
                        let data = {};
                        try { data = text ? JSON.parse(text) : {}; } catch(e) {}

                        if (!res.ok) {
                            throw new Error(data.message || `Request failed (${res.status})`);
                        }

                        return data;
                    },

                    async loadUsers() {
                        try {
                            const data = await this.request('/api/users', { method: 'GET' });
                            this.users = data.users || [];
                        } catch (e) {
                            this.error = e.message;
                        }
                    },

                    filteredUsers() {
                        const q = this.search.trim().toLowerCase();
                        if (!q) return this.users;
                        return this.users.filter(u =>
                            (u.name || '').toLowerCase().includes(q) ||
                            (u.email || '').toLowerCase().includes(q)
                        );
                    },

                    openCreate() {
                        this.modal.open = true;
                        this.modal.mode = 'create';
                        this.modal.id = null;
                        this.modal.form = { name:'', email:'', role:'trainer', password:'', password_confirmation:'' };
                    },

                    openEdit(user) {
                        this.modal.open = true;
                        this.modal.mode = 'edit';
                        this.modal.id = user.id;
                        this.modal.form = {
                            name: user.name,
                            email: user.email,
                            role: user.role,
                            password: '',
                            password_confirmation: '',
                        };
                    },

                    closeModal() {
                        this.modal.open = false;
                    },

                    async submitModal() {
                        try {
                            if (this.modal.mode === 'create') {
                                const data = await this.request('/api/users', {
                                    method: 'POST',
                                    body: JSON.stringify(this.modal.form)
                                });
                                this.message = data.message || 'User created.';
                            } else {
                                const payload = { ...this.modal.form };
                                if (!payload.password) {
                                    delete payload.password;
                                    delete payload.password_confirmation;
                                }
                                const data = await this.request(`/api/users/${this.modal.id}`, {
                                    method: 'PUT',
                                    body: JSON.stringify(payload)
                                });
                                this.message = data.message || 'User updated.';
                            }

                            this.closeModal();
                            await this.loadUsers();
                        } catch (e) {
                            this.error = e.message;
                        }
                    },

                    async deleteUser(id) {
                        if (!confirm('Delete this user?')) return;
                        try {
                            const data = await this.request(`/api/users/${id}`, { method: 'DELETE' });
                            this.message = data.message || 'User deleted.';
                            await this.loadUsers();
                        } catch (e) {
                            this.error = e.message;
                        }
                    },

                    formatDate(v) {
                        if (!v) return '-';
                        return new Date(v).toLocaleDateString();
                    }
                }
            }
        </script>
    </div>
</x-app-layout>
