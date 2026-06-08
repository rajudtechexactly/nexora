<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="api-url" content="{{ config('mobile.api_url') }}">
    <meta name="app-name" content="{{ config('mobile.app_name') }}">
    <title>{{ config('mobile.app_name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { DEFAULT: '#2563eb', dark: '#1e40af', light: '#eff6ff' } } } }
        };
    </script>
    <style>
        [x-cloak] { display: none !important; }
        html, body { overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
        ::-webkit-scrollbar { width: 0; height: 0; }
    </style>
</head>
<body class="h-full bg-gray-100 text-gray-900 antialiased select-none">
@verbatim
<div x-data="nexora()" x-init="init()" x-cloak class="mx-auto flex h-full max-w-md flex-col bg-gray-100">

    <!-- Toast -->
    <div x-show="toast" x-transition
         class="fixed left-1/2 top-4 z-50 -translate-x-1/2 rounded-full bg-gray-900/90 px-4 py-2 text-sm text-white shadow-lg"
         x-text="toast"></div>

    <!-- ===================== SPLASH ===================== -->
    <template x-if="screen === 'splash'">
        <div class="flex h-full flex-col items-center justify-center gap-4 bg-brand text-white">
            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 text-4xl">⚡</div>
            <div class="text-3xl font-extrabold tracking-tight" x-text="appName"></div>
            <div class="text-sm text-white/80">Connect. Share. Belong.</div>
        </div>
    </template>

    <!-- ===================== LOGIN ===================== -->
    <template x-if="screen === 'login'">
        <div class="flex h-full flex-col justify-center px-6">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand text-3xl text-white">⚡</div>
                <h1 class="text-2xl font-extrabold text-brand" x-text="appName"></h1>
                <p class="text-sm text-gray-500">Sign in to continue</p>
            </div>
            <form @submit.prevent="doLogin()" class="space-y-3">
                <input x-model="forms.login.login" type="text" placeholder="Email or username" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <input x-model="forms.login.password" type="password" placeholder="Password" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <div class="text-right">
                    <button type="button" @click="go('forgot')" class="text-xs font-semibold text-brand">Forgot password?</button>
                </div>
                <p x-show="error" class="text-sm text-red-600" x-text="error"></p>
                <button type="submit" :disabled="loading"
                        class="w-full rounded-xl bg-brand py-3 font-semibold text-white disabled:opacity-50">
                    <span x-text="loading ? 'Signing in…' : 'Log In'"></span>
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">
                New here?
                <button @click="go('register')" class="font-semibold text-brand">Create an account</button>
            </p>
        </div>
    </template>

    <!-- ===================== REGISTER ===================== -->
    <template x-if="screen === 'register'">
        <div class="flex h-full flex-col overflow-y-auto px-6 py-10">
            <h1 class="mb-1 text-2xl font-extrabold text-brand">Create account</h1>
            <p class="mb-6 text-sm text-gray-500">Join <span x-text="appName"></span> today</p>
            <form @submit.prevent="doRegister()" class="space-y-3">
                <input x-model="forms.register.name" type="text" placeholder="Full name" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <input x-model="forms.register.username" type="text" placeholder="Username" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <input x-model="forms.register.email" type="email" placeholder="Email" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <input x-model="forms.register.password" type="password" placeholder="Password (min 8, letters + numbers)" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <input x-model="forms.register.password_confirmation" type="password" placeholder="Confirm password" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <template x-for="(msgs, field) in fieldErrors" :key="field">
                    <p class="text-sm text-red-600" x-text="msgs[0]"></p>
                </template>
                <button type="submit" :disabled="loading"
                        class="w-full rounded-xl bg-brand py-3 font-semibold text-white disabled:opacity-50">
                    <span x-text="loading ? 'Creating…' : 'Sign Up'"></span>
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">
                Already have an account?
                <button @click="go('login')" class="font-semibold text-brand">Log in</button>
            </p>
        </div>
    </template>

    <!-- ===================== VERIFY OTP ===================== -->
    <template x-if="screen === 'otp'">
        <div class="flex h-full flex-col justify-center px-6">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand text-3xl text-white">✉️</div>
                <h1 class="text-2xl font-extrabold text-brand">Verify your email</h1>
                <p class="mt-1 text-sm text-gray-500">Enter the code we sent to</p>
                <p class="text-sm font-semibold text-gray-700" x-text="forms.otp.email"></p>
            </div>
            <form @submit.prevent="doVerifyOtp()" class="space-y-3">
                <input x-model="forms.otp.otp" type="text" inputmode="numeric" autocomplete="one-time-code"
                       maxlength="6" placeholder="6-digit code" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-center text-lg tracking-[0.4em] outline-none focus:border-brand">
                <p x-show="error" class="text-sm text-red-600" x-text="error"></p>
                <button type="submit" :disabled="loading"
                        class="w-full rounded-xl bg-brand py-3 font-semibold text-white disabled:opacity-50">
                    <span x-text="loading ? 'Verifying…' : 'Verify & Continue'"></span>
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">
                Didn't get it?
                <button @click="doResendOtp()" class="font-semibold text-brand">Resend code</button>
            </p>
            <p class="mt-2 text-center text-sm text-gray-400">
                <button @click="go('login')" class="font-semibold">Back to login</button>
            </p>
        </div>
    </template>

    <!-- ===================== FORGOT PASSWORD ===================== -->
    <template x-if="screen === 'forgot'">
        <div class="flex h-full flex-col justify-center px-6">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand text-3xl text-white">🔑</div>
                <h1 class="text-2xl font-extrabold text-brand">Reset password</h1>
                <p class="mt-1 text-sm text-gray-500">We'll email you a verification code</p>
            </div>
            <form @submit.prevent="doForgot()" class="space-y-3">
                <input x-model="forms.forgot.email" type="email" placeholder="Your account email" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <p x-show="error" class="text-sm text-red-600" x-text="error"></p>
                <button type="submit" :disabled="loading"
                        class="w-full rounded-xl bg-brand py-3 font-semibold text-white disabled:opacity-50">
                    <span x-text="loading ? 'Sending…' : 'Send code'"></span>
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-400">
                <button @click="go('login')" class="font-semibold">Back to login</button>
            </p>
        </div>
    </template>

    <!-- ===================== RESET PASSWORD ===================== -->
    <template x-if="screen === 'reset'">
        <div class="flex h-full flex-col justify-center overflow-y-auto px-6 py-10">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand text-3xl text-white">🔐</div>
                <h1 class="text-2xl font-extrabold text-brand">Set a new password</h1>
                <p class="mt-1 text-sm text-gray-500">Enter the code sent to</p>
                <p class="text-sm font-semibold text-gray-700" x-text="forms.reset.email"></p>
            </div>
            <form @submit.prevent="doReset()" class="space-y-3">
                <input x-model="forms.reset.otp" type="text" inputmode="numeric" maxlength="6" placeholder="6-digit code" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-center text-lg tracking-[0.4em] outline-none focus:border-brand">
                <input x-model="forms.reset.password" type="password" placeholder="New password (min 8, letters + numbers)" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <input x-model="forms.reset.password_confirmation" type="password" placeholder="Confirm new password" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand">
                <template x-for="(msgs, field) in fieldErrors" :key="field">
                    <p class="text-sm text-red-600" x-text="msgs[0]"></p>
                </template>
                <button type="submit" :disabled="loading"
                        class="w-full rounded-xl bg-brand py-3 font-semibold text-white disabled:opacity-50">
                    <span x-text="loading ? 'Resetting…' : 'Reset password'"></span>
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">
                Didn't get it?
                <button @click="doForgot()" class="font-semibold text-brand">Resend code</button>
            </p>
        </div>
    </template>

    <!-- ===================== MAIN (tabbed) ===================== -->
    <template x-if="authed">
        <div class="flex h-full flex-col">
            <!-- App bar -->
            <header class="flex items-center justify-between bg-brand px-4 py-3 text-white shadow">
                <div class="text-xl font-extrabold" x-text="appName"></div>
                <button @click="go('settings')" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15">⚙️</button>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto pb-2">

                <!-- HOME / SEARCH -->
                <section x-show="tab === 'home'" class="space-y-3 p-3">
                    <div class="flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm">
                        <span class="text-gray-400">🔍</span>
                        <input x-model="searchQuery" @input.debounce.400ms="doSearch()" type="text"
                               placeholder="Search people"
                               class="flex-1 bg-transparent text-sm outline-none">
                        <button x-show="searchQuery" @click="searchQuery=''; searchResults=[]" class="text-gray-400">✕</button>
                    </div>

                    <!-- Search results -->
                    <div x-show="searchQuery.length" class="space-y-2">
                        <template x-for="u in searchResults" :key="u.id">
                            <div class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                                <div @click="viewUser(u.username)" class="h-11 w-11 shrink-0 cursor-pointer overflow-hidden rounded-full bg-gray-200">
                                    <img :src="u.avatar_url || placeholder(u.name)" class="h-full w-full object-cover">
                                </div>
                                <div @click="viewUser(u.username)" class="flex-1 cursor-pointer">
                                    <div class="font-semibold" x-text="u.name"></div>
                                    <div class="text-xs text-gray-500" x-text="'@'+u.username"></div>
                                </div>
                                <button @click="viewUser(u.username)" class="rounded-lg bg-brand-light px-3 py-1.5 text-xs font-semibold text-brand">View</button>
                            </div>
                        </template>
                        <p x-show="!searchResults.length && !loading" class="py-6 text-center text-sm text-gray-400">No people found.</p>
                    </div>

                    <!-- Suggestions (when not searching) -->
                    <div x-show="!searchQuery.length">
                        <h2 class="px-1 pb-1 pt-2 text-sm font-bold text-gray-600">People you may know</h2>
                        <div class="space-y-2">
                            <template x-for="u in suggestions" :key="u.id">
                                <div class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                                    <div @click="viewUser(u.username)" class="h-11 w-11 shrink-0 cursor-pointer overflow-hidden rounded-full bg-gray-200">
                                        <img :src="u.avatar_url || placeholder(u.name)" class="h-full w-full object-cover">
                                    </div>
                                    <div @click="viewUser(u.username)" class="flex-1 cursor-pointer">
                                        <div class="font-semibold" x-text="u.name"></div>
                                        <div class="text-xs text-gray-500" x-text="'@'+u.username"></div>
                                    </div>
                                    <button @click="sendRequest(u.id, u)" class="rounded-lg bg-brand px-3 py-1.5 text-xs font-semibold text-white">Add</button>
                                </div>
                            </template>
                            <p x-show="!suggestions.length && !loading" class="py-10 text-center text-sm text-gray-400">No suggestions right now.</p>
                        </div>
                    </div>
                </section>

                <!-- FRIENDS -->
                <section x-show="tab === 'friends'" class="space-y-2 p-3">
                    <h2 class="px-1 pb-1 text-sm font-bold text-gray-600">
                        Your friends <span x-text="'('+friends.length+')'" class="text-gray-400"></span>
                    </h2>
                    <template x-for="u in friends" :key="u.id">
                        <div class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                            <div @click="viewUser(u.username)" class="h-11 w-11 shrink-0 cursor-pointer overflow-hidden rounded-full bg-gray-200">
                                <img :src="u.avatar_url || placeholder(u.name)" class="h-full w-full object-cover">
                            </div>
                            <div @click="viewUser(u.username)" class="flex-1 cursor-pointer">
                                <div class="font-semibold" x-text="u.name"></div>
                                <div class="text-xs text-gray-500" x-text="'@'+u.username"></div>
                            </div>
                            <button @click="unfriend(u.id)" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">Unfriend</button>
                        </div>
                    </template>
                    <p x-show="!friends.length && !loading" class="py-10 text-center text-sm text-gray-400">No friends yet. Find people from Home.</p>
                </section>

                <!-- REQUESTS -->
                <section x-show="tab === 'requests'" class="space-y-4 p-3">
                    <div>
                        <h2 class="px-1 pb-1 text-sm font-bold text-gray-600">Friend requests</h2>
                        <div class="space-y-2">
                            <template x-for="r in incoming" :key="r.id">
                                <div class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                                    <div class="h-11 w-11 shrink-0 overflow-hidden rounded-full bg-gray-200">
                                        <img :src="r.user.avatar_url || placeholder(r.user.name)" class="h-full w-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold" x-text="r.user.name"></div>
                                        <div class="text-xs text-gray-500" x-text="'@'+r.user.username"></div>
                                    </div>
                                    <button @click="accept(r.user.id)" class="rounded-lg bg-brand px-3 py-1.5 text-xs font-semibold text-white">Confirm</button>
                                    <button @click="decline(r.user.id)" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">Delete</button>
                                </div>
                            </template>
                            <p x-show="!incoming.length && !loading" class="py-6 text-center text-sm text-gray-400">No pending requests.</p>
                        </div>
                    </div>
                    <div x-show="outgoing.length">
                        <h2 class="px-1 pb-1 text-sm font-bold text-gray-600">Sent requests</h2>
                        <div class="space-y-2">
                            <template x-for="r in outgoing" :key="r.id">
                                <div class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                                    <div class="h-11 w-11 shrink-0 overflow-hidden rounded-full bg-gray-200">
                                        <img :src="r.user.avatar_url || placeholder(r.user.name)" class="h-full w-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold" x-text="r.user.name"></div>
                                        <div class="text-xs text-gray-500">Request sent</div>
                                    </div>
                                    <button @click="cancel(r.user.id)" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">Cancel</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                <!-- MY PROFILE -->
                <section x-show="tab === 'profile' && me" class="pb-4">
                    <div class="h-28 bg-gradient-to-r from-brand to-brand-dark"
                         :style="me && me.profile.cover_url ? `background-image:url(${me.profile.cover_url});background-size:cover;background-position:center` : ''"></div>
                    <div class="px-4">
                        <div class="-mt-10 mb-2 flex items-end justify-between">
                            <div class="relative">
                                <div class="h-20 w-20 overflow-hidden rounded-full border-4 border-white bg-gray-200">
                                    <img :src="(me && me.profile.avatar_url) || placeholder(me ? me.name : '')" class="h-full w-full object-cover">
                                </div>
                                <label class="absolute bottom-0 right-0 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-brand text-xs text-white">
                                    📷<input type="file" accept="image/*" class="hidden" @change="uploadAvatar($event)">
                                </label>
                            </div>
                            <button @click="openEdit()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Edit</button>
                        </div>
                        <h1 class="text-xl font-extrabold" x-text="me ? me.name : ''"></h1>
                        <p class="text-sm text-gray-500" x-text="me ? '@'+me.username : ''"></p>
                        <p class="mt-2 text-sm" x-text="me && me.profile.bio ? me.profile.bio : 'No bio yet.'"></p>
                        <div class="mt-3 flex gap-6 text-sm">
                            <div><span class="font-bold" x-text="me && me.stats ? me.stats.friends : 0"></span> <span class="text-gray-500">Friends</span></div>
                            <div><span class="font-bold" x-text="me && me.stats ? me.stats.posts : 0"></span> <span class="text-gray-500">Posts</span></div>
                        </div>
                        <div class="mt-3 space-y-1 text-sm text-gray-600">
                            <p x-show="me && me.profile.location">📍 <span x-text="me ? me.profile.location : ''"></span></p>
                            <p x-show="me && me.profile.work">💼 <span x-text="me ? me.profile.work : ''"></span></p>
                            <p x-show="me && me.profile.website">🔗 <span x-text="me ? me.profile.website : ''"></span></p>
                        </div>
                    </div>
                </section>
            </main>

            <!-- Bottom tab bar -->
            <nav class="grid grid-cols-4 border-t border-gray-200 bg-white">
                <button @click="setTab('home')" :class="tab==='home' ? 'text-brand' : 'text-gray-400'" class="flex flex-col items-center py-2 text-xs">
                    <span class="text-lg">🏠</span>Home
                </button>
                <button @click="setTab('friends')" :class="tab==='friends' ? 'text-brand' : 'text-gray-400'" class="flex flex-col items-center py-2 text-xs">
                    <span class="text-lg">👥</span>Friends
                </button>
                <button @click="setTab('requests')" :class="tab==='requests' ? 'text-brand' : 'text-gray-400'" class="relative flex flex-col items-center py-2 text-xs">
                    <span class="text-lg">🔔</span>Requests
                    <span x-show="incoming.length" class="absolute right-5 top-1 rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white" x-text="incoming.length"></span>
                </button>
                <button @click="setTab('profile')" :class="tab==='profile' ? 'text-brand' : 'text-gray-400'" class="flex flex-col items-center py-2 text-xs">
                    <span class="text-lg">👤</span>Profile
                </button>
            </nav>
        </div>
    </template>

    <!-- ===================== OTHER USER PROFILE (overlay) ===================== -->
    <template x-if="screen === 'userProfile' && viewedUser">
        <div class="fixed inset-0 z-40 mx-auto flex max-w-md flex-col bg-gray-100">
            <header class="flex items-center gap-3 bg-brand px-4 py-3 text-white shadow">
                <button @click="closeUser()" class="text-xl">←</button>
                <div class="font-bold" x-text="viewedUser.name"></div>
            </header>
            <div class="flex-1 overflow-y-auto pb-6">
                <div class="h-28 bg-gradient-to-r from-brand to-brand-dark"
                     :style="viewedUser.profile.cover_url ? `background-image:url(${viewedUser.profile.cover_url});background-size:cover;background-position:center` : ''"></div>
                <div class="px-4">
                    <div class="-mt-10 mb-2 h-20 w-20 overflow-hidden rounded-full border-4 border-white bg-gray-200">
                        <img :src="viewedUser.profile.avatar_url || placeholder(viewedUser.name)" class="h-full w-full object-cover">
                    </div>
                    <h1 class="text-xl font-extrabold" x-text="viewedUser.name"></h1>
                    <p class="text-sm text-gray-500" x-text="'@'+viewedUser.username"></p>
                    <p class="mt-2 text-sm" x-text="viewedUser.profile.bio || ''"></p>
                    <div class="mt-3 flex gap-6 text-sm">
                        <div><span class="font-bold" x-text="viewedUser.stats ? viewedUser.stats.friends : 0"></span> <span class="text-gray-500">Friends</span></div>
                    </div>

                    <!-- Relationship action -->
                    <div class="mt-4">
                        <button x-show="viewedUser.friendship_status==='none'" @click="sendRequest(viewedUser.id)"
                                class="w-full rounded-xl bg-brand py-2.5 font-semibold text-white">Add Friend</button>
                        <button x-show="viewedUser.friendship_status==='pending_outgoing'" @click="cancel(viewedUser.id)"
                                class="w-full rounded-xl bg-gray-200 py-2.5 font-semibold text-gray-700">Cancel Request</button>
                        <div x-show="viewedUser.friendship_status==='pending_incoming'" class="flex gap-2">
                            <button @click="accept(viewedUser.id)" class="flex-1 rounded-xl bg-brand py-2.5 font-semibold text-white">Confirm</button>
                            <button @click="decline(viewedUser.id)" class="flex-1 rounded-xl bg-gray-200 py-2.5 font-semibold text-gray-700">Delete</button>
                        </div>
                        <button x-show="viewedUser.friendship_status==='friends'" @click="unfriend(viewedUser.id)"
                                class="w-full rounded-xl bg-gray-200 py-2.5 font-semibold text-gray-700">✓ Friends</button>
                        <button x-show="viewedUser.friendship_status==='blocked'" @click="unblock(viewedUser.id)"
                                class="w-full rounded-xl bg-red-100 py-2.5 font-semibold text-red-600">Unblock</button>
                        <button x-show="['none','friends','pending_outgoing','pending_incoming'].includes(viewedUser.friendship_status)"
                                @click="block(viewedUser.id)"
                                class="mt-2 w-full rounded-xl border border-red-200 py-2.5 text-sm font-semibold text-red-500">Block</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ===================== EDIT PROFILE (overlay) ===================== -->
    <template x-if="screen === 'editProfile'">
        <div class="fixed inset-0 z-40 mx-auto flex max-w-md flex-col bg-gray-100">
            <header class="flex items-center justify-between bg-brand px-4 py-3 text-white shadow">
                <button @click="go('main')" class="text-xl">←</button>
                <div class="font-bold">Edit profile</div>
                <button @click="saveProfile()" :disabled="loading" class="font-semibold">Save</button>
            </header>
            <div class="flex-1 space-y-3 overflow-y-auto p-4">
                <label class="block text-xs font-semibold text-gray-500">Name</label>
                <input x-model="forms.edit.name" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand">
                <label class="block text-xs font-semibold text-gray-500">Bio</label>
                <textarea x-model="forms.edit.bio" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand"></textarea>
                <label class="block text-xs font-semibold text-gray-500">Location</label>
                <input x-model="forms.edit.location" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand">
                <label class="block text-xs font-semibold text-gray-500">Work</label>
                <input x-model="forms.edit.work" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand">
                <label class="block text-xs font-semibold text-gray-500">Website</label>
                <input x-model="forms.edit.website" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand">
            </div>
        </div>
    </template>

    <!-- ===================== SETTINGS (overlay) ===================== -->
    <template x-if="screen === 'settings'">
        <div class="fixed inset-0 z-40 mx-auto flex max-w-md flex-col bg-gray-100">
            <header class="flex items-center gap-3 bg-brand px-4 py-3 text-white shadow">
                <button @click="go('main')" class="text-xl">←</button>
                <div class="font-bold">Settings</div>
            </header>
            <div class="flex-1 space-y-4 overflow-y-auto p-4">
                <div class="rounded-xl bg-white p-4 shadow-sm">
                    <h3 class="mb-2 text-sm font-bold text-gray-600">Change password</h3>
                    <div class="space-y-2">
                        <input x-model="forms.password.current_password" type="password" placeholder="Current password"
                               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand">
                        <input x-model="forms.password.password" type="password" placeholder="New password"
                               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand">
                        <input x-model="forms.password.password_confirmation" type="password" placeholder="Confirm new password"
                               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-brand">
                        <button @click="changePassword()" :disabled="loading" class="w-full rounded-xl bg-brand py-2.5 font-semibold text-white disabled:opacity-50">Update password</button>
                    </div>
                </div>
                <button @click="logout()" class="w-full rounded-xl bg-white py-3 font-semibold text-red-500 shadow-sm">Log out</button>
                <p class="text-center text-xs text-gray-400" x-text="appName + ' • v1.0.0'"></p>
            </div>
        </div>
    </template>

</div>
@endverbatim

<script>
    function nexora() {
        const API = document.querySelector('meta[name="api-url"]').content.replace(/\/$/, '');
        const APP_NAME = document.querySelector('meta[name="app-name"]').content;
        const TOKEN_KEY = 'nexora_token';

        return {
            appName: APP_NAME,
            screen: 'splash',
            tab: 'home',
            token: null,
            me: null,
            loading: false,
            error: '',
            fieldErrors: {},
            toast: '',
            searchQuery: '',
            searchResults: [],
            suggestions: [],
            friends: [],
            incoming: [],
            outgoing: [],
            viewedUser: null,
            forms: {
                login: { login: '', password: '' },
                register: { name: '', username: '', email: '', password: '', password_confirmation: '' },
                otp: { email: '', otp: '' },
                forgot: { email: '' },
                reset: { email: '', otp: '', password: '', password_confirmation: '' },
                edit: { name: '', bio: '', location: '', work: '', website: '' },
                password: { current_password: '', password: '', password_confirmation: '' },
            },

            get authed() { return this.screen === 'main'; },

            async init() {
                this.token = localStorage.getItem(TOKEN_KEY);
                // brief splash
                await new Promise(r => setTimeout(r, 600));
                if (this.token) {
                    const ok = await this.loadMe();
                    if (ok) { this.screen = 'main'; this.bootMain(); return; }
                }
                this.screen = 'login';
            },

            // ---- HTTP ----
            async api(method, path, body = null, isForm = false) {
                const headers = { 'Accept': 'application/json' };
                if (this.token) headers['Authorization'] = 'Bearer ' + this.token;
                let payload = null;
                if (body && isForm) { payload = body; }
                else if (body) { headers['Content-Type'] = 'application/json'; payload = JSON.stringify(body); }

                let res = await fetch(API + path, { method, headers, body: payload });

                if (res.status === 401 && this.token && path !== '/auth/refresh') {
                    const refreshed = await this.tryRefresh();
                    if (refreshed) return this.api(method, path, body, isForm);
                }
                const json = await res.json().catch(() => ({}));
                return { ok: res.ok, status: res.status, json };
            },

            async tryRefresh() {
                const res = await fetch(API + '/auth/refresh', {
                    method: 'POST', headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token },
                });
                if (!res.ok) { this.forceLogout(); return false; }
                const json = await res.json();
                this.token = json.data.access_token;
                localStorage.setItem(TOKEN_KEY, this.token);
                return true;
            },

            // ---- Auth ----
            async doLogin() {
                this.loading = true; this.error = '';
                const { ok, status, json } = await this.api('POST', '/auth/login', this.forms.login);
                this.loading = false;
                // Account exists but email isn't verified yet → go enter the OTP.
                if (status === 403 && json.code === 'email_not_verified') {
                    this.forms.otp = { email: (json.errors && json.errors.email && json.errors.email[0]) || this.forms.login.login, otp: '' };
                    this.showToast(json.message || 'Verify your email to continue.');
                    this.go('otp');
                    return;
                }
                if (!ok) { this.error = json.message || 'Login failed.'; return; }
                this.finishAuth(json);
            },

            async doRegister() {
                this.loading = true; this.fieldErrors = {}; this.error = '';
                const { ok, json } = await this.api('POST', '/auth/register', this.forms.register);
                this.loading = false;
                if (!ok) { this.fieldErrors = json.errors || { _: [json.message] }; return; }
                // No token yet — verify the emailed OTP to finish signing in.
                this.forms.otp = { email: this.forms.register.email, otp: '' };
                this.showToast(json.message || 'We sent a verification code to your email.');
                this.go('otp');
            },

            async doVerifyOtp() {
                this.loading = true; this.error = '';
                const { ok, json } = await this.api('POST', '/auth/verify-otp', this.forms.otp);
                this.loading = false;
                if (!ok) { this.error = this.firstError(json); return; }
                this.showToast('Welcome to ' + this.appName + '!');
                this.finishAuth(json);
            },

            async doResendOtp() {
                this.error = '';
                const { json } = await this.api('POST', '/auth/resend-otp', { email: this.forms.otp.email });
                this.showToast(json.message || 'A new code has been sent.');
            },

            async doForgot() {
                this.loading = true; this.error = '';
                const { json } = await this.api('POST', '/auth/forgot-password', this.forms.forgot);
                this.loading = false;
                this.forms.reset = { email: this.forms.forgot.email, otp: '', password: '', password_confirmation: '' };
                this.showToast(json.message || 'If that email exists, a reset code has been sent.');
                this.go('reset');
            },

            async doReset() {
                this.loading = true; this.error = ''; this.fieldErrors = {};
                const { ok, json } = await this.api('POST', '/auth/reset-password', this.forms.reset);
                this.loading = false;
                if (!ok) { this.fieldErrors = json.errors || {}; this.error = this.firstError(json); return; }
                this.showToast('Password reset. Please log in.');
                this.forms.login = { login: this.forms.reset.email, password: '' };
                this.go('login');
            },

            // Persist token + user and enter the app.
            finishAuth(json) {
                this.token = json.data.access_token;
                localStorage.setItem(TOKEN_KEY, this.token);
                this.me = json.data.user;
                this.screen = 'main'; this.bootMain();
            },

            async logout() {
                await this.api('POST', '/auth/logout').catch(() => {});
                this.forceLogout();
            },

            forceLogout() {
                localStorage.removeItem(TOKEN_KEY);
                this.token = null; this.me = null;
                this.screen = 'login';
                this.forms.login = { login: '', password: '' };
            },

            // ---- Bootstrap main data ----
            bootMain() {
                this.tab = 'home';
                this.loadSuggestions();
                this.loadFriends();
                this.loadRequests();
            },

            async loadMe() {
                const { ok, json } = await this.api('GET', '/auth/me');
                if (ok) { this.me = json.data; return true; }
                return false;
            },

            // ---- Discovery ----
            async loadSuggestions() {
                const { ok, json } = await this.api('GET', '/friends/suggestions');
                if (ok) this.suggestions = json.data || [];
            },

            async doSearch() {
                if (!this.searchQuery.trim()) { this.searchResults = []; return; }
                const { ok, json } = await this.api('GET', '/users/search?q=' + encodeURIComponent(this.searchQuery));
                if (ok) this.searchResults = json.data || [];
            },

            // ---- Friends ----
            async loadFriends() {
                const { ok, json } = await this.api('GET', '/friends');
                if (ok) this.friends = json.data || [];
            },
            async loadRequests() {
                const inc = await this.api('GET', '/friends/requests/incoming');
                if (inc.ok) this.incoming = inc.json.data || [];
                const out = await this.api('GET', '/friends/requests/outgoing');
                if (out.ok) this.outgoing = out.json.data || [];
            },

            async sendRequest(id, fromList = null) {
                const { ok, json } = await this.api('POST', '/friends/requests/' + id);
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.showToast('Friend request sent');
                if (fromList) this.suggestions = this.suggestions.filter(u => u.id !== id);
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'pending_outgoing';
                this.loadRequests();
            },
            async accept(id) {
                const { ok, json } = await this.api('POST', '/friends/requests/' + id + '/accept');
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.showToast('You are now friends');
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'friends';
                this.loadRequests(); this.loadFriends();
            },
            async decline(id) {
                await this.api('POST', '/friends/requests/' + id + '/decline');
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none';
                this.loadRequests();
            },
            async cancel(id) {
                await this.api('DELETE', '/friends/requests/' + id);
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none';
                this.loadRequests();
            },
            async unfriend(id) {
                await this.api('DELETE', '/friends/' + id);
                this.showToast('Removed from friends');
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none';
                this.loadFriends();
            },
            async block(id) {
                await this.api('POST', '/friends/' + id + '/block');
                this.showToast('User blocked');
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'blocked';
                this.loadFriends(); this.loadRequests();
            },
            async unblock(id) {
                await this.api('DELETE', '/friends/' + id + '/block');
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none';
                this.showToast('User unblocked');
            },

            // ---- Profiles ----
            async viewUser(username) {
                const { ok, json } = await this.api('GET', '/users/' + username);
                if (!ok) { this.showToast('Could not load profile'); return; }
                this.viewedUser = json.data;
                this.screen = 'userProfile';
            },
            closeUser() { this.viewedUser = null; this.screen = 'main'; },

            openEdit() {
                this.forms.edit = {
                    name: this.me.name || '',
                    bio: this.me.profile.bio || '',
                    location: this.me.profile.location || '',
                    work: this.me.profile.work || '',
                    website: this.me.profile.website || '',
                };
                this.screen = 'editProfile';
            },
            async saveProfile() {
                this.loading = true;
                const { ok, json } = await this.api('PATCH', '/profile', this.forms.edit);
                this.loading = false;
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.me = json.data;
                this.showToast('Profile updated');
                this.screen = 'main'; this.tab = 'profile';
            },
            async uploadAvatar(event) {
                const file = event.target.files[0];
                if (!file) return;
                const fd = new FormData(); fd.append('image', file);
                const { ok, json } = await this.api('POST', '/profile/avatar', fd, true);
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.me = json.data;
                this.showToast('Photo updated');
            },
            async changePassword() {
                this.loading = true;
                const { ok, json } = await this.api('POST', '/auth/change-password', this.forms.password);
                this.loading = false;
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.forms.password = { current_password: '', password: '', password_confirmation: '' };
                this.showToast('Password changed');
            },

            // ---- UI helpers ----
            setTab(t) {
                this.tab = t;
                if (t === 'friends') this.loadFriends();
                if (t === 'requests') this.loadRequests();
                if (t === 'profile') this.loadMe();
                if (t === 'home') this.loadSuggestions();
            },
            go(s) {
                if (s === 'main') { this.screen = 'main'; return; }
                this.error = ''; this.fieldErrors = {};
                this.screen = s;
            },
            placeholder(name) {
                const initials = (name || '?').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
                return 'data:image/svg+xml;utf8,' + encodeURIComponent(
                    `<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#dbeafe"/><text x="50" y="58" font-size="38" text-anchor="middle" fill="#2563eb" font-family="Arial">${initials}</text></svg>`
                );
            },
            firstError(json) {
                if (json.errors) { const k = Object.keys(json.errors)[0]; return json.errors[k][0]; }
                return json.message || 'Something went wrong.';
            },
            showToast(msg) { this.toast = msg; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2200); },
        };
    }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</body>
</html>
