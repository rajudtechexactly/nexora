<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="api-url" content="{{ config('mobile.api_url') }}">
    <meta name="app-name" content="{{ config('mobile.app_name') }}">
    <meta name="reverb-key" content="{{ config('mobile.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('mobile.reverb.ws_host') }}">
    <meta name="reverb-port" content="{{ config('mobile.reverb.ws_port') }}">
    <meta name="reverb-path" content="{{ config('mobile.reverb.ws_path') }}">
    <meta name="reverb-scheme" content="{{ config('mobile.reverb.scheme') }}">
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
         class="fixed left-1/2 top-4 z-[60] -translate-x-1/2 rounded-full bg-gray-900/90 px-4 py-2 text-sm text-white shadow-lg"
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
                <div class="flex items-center gap-2">
                    <span class="text-xl font-extrabold" x-text="appName"></span>
                    <span class="h-2 w-2 rounded-full" :class="rt==='on' ? 'bg-green-400' : 'bg-white/30'"
                          :title="rt==='on' ? 'Live' : 'Offline'"></span>
                </div>
                <button @click="go('settings')" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15">⚙️</button>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto pb-2"
                  @touchstart.passive="ptrStart($event)" @touchmove.passive="ptrMove($event)" @touchend="ptrEnd()">
                <!-- Pull-to-refresh indicator -->
                <div class="flex items-center justify-center overflow-hidden text-xs text-gray-400"
                     :style="`height:${refreshing ? 36 : pull}px`">
                    <span x-show="refreshing">↻ Refreshing…</span>
                    <span x-show="!refreshing && pull > 0" x-text="pull > 70 ? '↑ Release to refresh' : '↓ Pull to refresh'"></span>
                </div>

                <!-- ===== FEED ===== -->
                <section x-show="tab === 'feed'" class="space-y-3 p-3">
                    <!-- Composer -->
                    <div class="rounded-xl bg-white p-3 shadow-sm">
                        <textarea x-model="composer.content" rows="2" placeholder="What's on your mind?"
                                  class="w-full resize-none bg-transparent text-sm outline-none"></textarea>
                        <div x-show="composer.previews.length" class="flex flex-wrap gap-2 py-2">
                            <template x-for="(p, i) in composer.previews" :key="i">
                                <div class="relative h-16 w-16 overflow-hidden rounded-lg bg-gray-100">
                                    <img :src="p" class="h-full w-full object-cover">
                                    <button @click="removeComposerImage(i)" class="absolute right-0 top-0 bg-black/50 px-1 text-xs text-white">✕</button>
                                </div>
                            </template>
                        </div>
                        <div class="mt-1 flex items-center justify-between border-t border-gray-100 pt-2">
                            <div class="flex items-center gap-3">
                                <label class="cursor-pointer text-lg">🖼️
                                    <input type="file" accept="image/*" multiple class="hidden" @change="pickComposerImages($event)">
                                </label>
                                <select x-model="composer.visibility" class="rounded-lg bg-gray-100 px-2 py-1 text-xs text-gray-600 outline-none">
                                    <option value="friends">Friends</option>
                                    <option value="public">Public</option>
                                    <option value="private">Only me</option>
                                </select>
                            </div>
                            <button @click="submitPost()" :disabled="loading"
                                    class="rounded-lg bg-brand px-4 py-1.5 text-sm font-semibold text-white disabled:opacity-50">Post</button>
                        </div>
                    </div>

                    <!-- Feed list -->
                    <template x-for="post in feed" :key="post.id">
                        <div class="rounded-xl bg-white shadow-sm">
                            <div class="flex items-center gap-3 p-3">
                                <div @click="viewUser(post.author.username)" class="h-10 w-10 shrink-0 cursor-pointer overflow-hidden rounded-full bg-gray-200">
                                    <img :src="post.author.avatar_url || placeholder(post.author.name)" class="h-full w-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold" x-text="post.author.name"></div>
                                    <div class="text-xs text-gray-400" x-text="timeAgo(post.created_at) + ' · ' + post.visibility"></div>
                                </div>
                                <button x-show="post.can_edit" @click="deletePost(post)" class="text-gray-300">🗑️</button>
                            </div>
                            <p x-show="post.content" class="px-3 pb-2 text-sm whitespace-pre-wrap" x-text="post.content"></p>
                            <template x-if="post.media && post.media.length">
                                <div class="grid gap-0.5" :class="post.media.length === 1 ? 'grid-cols-1' : 'grid-cols-2'">
                                    <template x-for="m in post.media" :key="m.id">
                                        <img :src="m.url" class="max-h-72 w-full object-cover">
                                    </template>
                                </div>
                            </template>
                            <div class="flex items-center justify-between px-3 py-1 text-xs text-gray-400">
                                <span x-text="post.reactions_count + ' reactions'"></span>
                                <span x-text="post.comments_count + ' comments'"></span>
                            </div>
                            <div class="grid grid-cols-2 border-t border-gray-100 text-sm font-semibold">
                                <button @click="toggleLike(post)"
                                        :class="post.my_reaction ? 'text-brand' : 'text-gray-500'"
                                        class="py-2">👍 Like</button>
                                <button @click="openComments(post)" class="py-2 text-gray-500">💬 Comment</button>
                            </div>
                        </div>
                    </template>
                    <p x-show="!feed.length && !loading" class="py-10 text-center text-sm text-gray-400">
                        Your feed is empty. Create a post or add friends!
                    </p>
                </section>

                <!-- ===== PEOPLE (search + suggestions + friends + requests) ===== -->
                <section x-show="tab === 'people'" class="space-y-3 p-3">
                    <div class="flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-sm">
                        <span class="text-gray-400">🔍</span>
                        <input x-model="searchQuery" @input.debounce.400ms="doSearch()" type="text" placeholder="Search people"
                               class="flex-1 bg-transparent text-sm outline-none">
                        <button x-show="searchQuery" @click="searchQuery=''; searchResults=[]" class="text-gray-400">✕</button>
                    </div>

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

                    <div x-show="!searchQuery.length" class="space-y-4">
                        <div x-show="incoming.length">
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
                            </div>
                        </div>

                        <div>
                            <h2 class="px-1 pb-1 text-sm font-bold text-gray-600">People you may know</h2>
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
                                        <button x-show="!isRequested(u.id)" @click="sendRequest(u.id)" class="rounded-lg bg-brand px-3 py-1.5 text-xs font-semibold text-white">Add</button>
                                        <button x-show="isRequested(u.id)" @click="cancel(u.id)" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">Cancel</button>
                                    </div>
                                </template>
                                <p x-show="!suggestions.length && !loading" class="py-4 text-center text-sm text-gray-400">No suggestions right now.</p>
                            </div>
                        </div>

                        <div>
                            <h2 class="px-1 pb-1 text-sm font-bold text-gray-600">Your friends <span class="text-gray-400" x-text="'('+friends.length+')'"></span></h2>
                            <div class="space-y-2">
                                <template x-for="u in friends" :key="u.id">
                                    <div class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                                        <div @click="viewUser(u.username)" class="h-11 w-11 shrink-0 cursor-pointer overflow-hidden rounded-full bg-gray-200">
                                            <img :src="u.avatar_url || placeholder(u.name)" class="h-full w-full object-cover">
                                        </div>
                                        <div @click="viewUser(u.username)" class="flex-1 cursor-pointer">
                                            <div class="font-semibold" x-text="u.name"></div>
                                            <div class="text-xs text-gray-500" x-text="'@'+u.username"></div>
                                        </div>
                                        <button @click="openChatWith(u.id)" class="rounded-lg bg-brand-light px-3 py-1.5 text-xs font-semibold text-brand">Message</button>
                                    </div>
                                </template>
                                <p x-show="!friends.length && !loading" class="py-4 text-center text-sm text-gray-400">No friends yet.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ===== CHAT (conversation list) ===== -->
                <section x-show="tab === 'chat'" class="p-3">
                    <h2 class="px-1 pb-2 text-sm font-bold text-gray-600">Messages</h2>
                    <div class="space-y-2">
                        <template x-for="c in conversations" :key="c.id">
                            <div @click="openConversation(c)" class="flex cursor-pointer items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full bg-gray-200">
                                    <img :src="(c.other && c.other.avatar_url) || placeholder(c.other ? c.other.name : '?')" class="h-full w-full object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex justify-between">
                                        <span class="truncate font-semibold" x-text="c.other ? c.other.name : 'Conversation'"></span>
                                        <span class="ml-2 shrink-0 text-[10px] text-gray-400" x-text="c.last_message_at ? timeAgo(c.last_message_at) : ''"></span>
                                    </div>
                                    <div class="truncate text-xs text-gray-500" x-text="c.last_message ? c.last_message.body || 'Attachment' : 'Say hi 👋'"></div>
                                </div>
                                <span x-show="c.unread_count" class="rounded-full bg-brand px-2 py-0.5 text-[10px] font-bold text-white" x-text="c.unread_count"></span>
                            </div>
                        </template>
                        <p x-show="!conversations.length && !loading" class="py-10 text-center text-sm text-gray-400">
                            No conversations yet. Message a friend from People.
                        </p>
                    </div>
                </section>

                <!-- ===== NOTIFICATIONS ===== -->
                <section x-show="tab === 'alerts'" class="p-3">
                    <div class="flex items-center justify-between px-1 pb-2">
                        <h2 class="text-sm font-bold text-gray-600">Notifications</h2>
                        <button @click="markAllRead()" class="text-xs font-semibold text-brand">Mark all read</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="n in notifications" :key="n.id">
                            <div class="flex items-center gap-3 rounded-xl p-3 shadow-sm"
                                 :class="n.read ? 'bg-white' : 'bg-brand-light'">
                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-gray-200">
                                    <img :src="(n.actor && n.actor.avatar_url) || placeholder(n.actor ? n.actor.name : '?')" class="h-full w-full object-cover">
                                </div>
                                <div class="flex-1 text-sm">
                                    <span class="font-semibold" x-text="n.actor ? n.actor.name : 'Someone'"></span>
                                    <span x-text="' ' + notifText(n)"></span>
                                    <div class="text-[10px] text-gray-400" x-text="timeAgo(n.created_at)"></div>
                                </div>
                            </div>
                        </template>
                        <p x-show="!notifications.length && !loading" class="py-10 text-center text-sm text-gray-400">No notifications yet.</p>
                    </div>
                </section>

                <!-- ===== MY PROFILE ===== -->
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
            <nav class="grid grid-cols-5 border-t border-gray-200 bg-white">
                <button @click="setTab('feed')" :class="tab==='feed' ? 'text-brand' : 'text-gray-400'" class="flex flex-col items-center py-2 text-[11px]">
                    <span class="text-lg">🏠</span>Feed
                </button>
                <button @click="setTab('people')" :class="tab==='people' ? 'text-brand' : 'text-gray-400'" class="relative flex flex-col items-center py-2 text-[11px]">
                    <span class="text-lg">👥</span>People
                    <span x-show="incoming.length" class="absolute right-4 top-1 rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white" x-text="incoming.length"></span>
                </button>
                <button @click="setTab('chat')" :class="tab==='chat' ? 'text-brand' : 'text-gray-400'" class="relative flex flex-col items-center py-2 text-[11px]">
                    <span class="text-lg">💬</span>Chat
                    <span x-show="chatUnread" class="absolute right-4 top-1 rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white" x-text="chatUnread"></span>
                </button>
                <button @click="setTab('alerts')" :class="tab==='alerts' ? 'text-brand' : 'text-gray-400'" class="relative flex flex-col items-center py-2 text-[11px]">
                    <span class="text-lg">🔔</span>Alerts
                    <span x-show="unread" class="absolute right-4 top-1 rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white" x-text="unread"></span>
                </button>
                <button @click="setTab('profile')" :class="tab==='profile' ? 'text-brand' : 'text-gray-400'" class="flex flex-col items-center py-2 text-[11px]">
                    <span class="text-lg">👤</span>Profile
                </button>
            </nav>
        </div>
    </template>

    <!-- ===================== COMMENTS (overlay) ===================== -->
    <template x-if="screen === 'comments' && activePost">
        <div class="fixed inset-0 z-40 mx-auto flex max-w-md flex-col bg-gray-100">
            <header class="flex items-center gap-3 bg-brand px-4 py-3 text-white shadow">
                <button @click="closeComments()" class="text-xl">←</button>
                <div class="font-bold">Comments</div>
            </header>
            <div class="flex-1 space-y-2 overflow-y-auto p-3">
                <template x-for="c in comments" :key="c.id">
                    <div>
                        <div class="flex items-start gap-2">
                            <div class="h-8 w-8 shrink-0 overflow-hidden rounded-full bg-gray-200">
                                <img :src="(c.author && c.author.avatar_url) || placeholder(c.author ? c.author.name : '?')" class="h-full w-full object-cover">
                            </div>
                            <div class="flex-1">
                                <div class="rounded-2xl bg-white px-3 py-2 text-sm shadow-sm">
                                    <span class="font-semibold" x-text="c.author ? c.author.name : ''"></span>
                                    <div x-text="c.content"></div>
                                </div>
                                <div class="px-3 text-[10px] text-gray-400" x-text="timeAgo(c.created_at)"></div>
                            </div>
                        </div>
                    </div>
                </template>
                <p x-show="!comments.length && !loading" class="py-8 text-center text-sm text-gray-400">No comments yet. Be the first!</p>
            </div>
            <div class="flex items-center gap-2 border-t border-gray-200 bg-white p-2">
                <input x-model="commentDraft" @keydown.enter="submitComment()" placeholder="Write a comment…"
                       class="flex-1 rounded-full bg-gray-100 px-4 py-2 text-sm outline-none">
                <button @click="submitComment()" class="rounded-full bg-brand px-4 py-2 text-sm font-semibold text-white">Send</button>
            </div>
        </div>
    </template>

    <!-- ===================== CONVERSATION (overlay) ===================== -->
    <template x-if="screen === 'conversation' && activeConv">
        <div class="fixed inset-0 z-40 mx-auto flex max-w-md flex-col bg-gray-100">
            <header class="flex items-center gap-3 bg-brand px-3 py-3 text-white shadow">
                <button @click="closeConversation()" class="text-xl">←</button>
                <div class="h-9 w-9 overflow-hidden rounded-full bg-white/20">
                    <img :src="(activeConv.other && activeConv.other.avatar_url) || placeholder(activeConv.other ? activeConv.other.name : '?')" class="h-full w-full object-cover">
                </div>
                <div class="flex-1 font-bold" x-text="activeConv.other ? activeConv.other.name : 'Chat'"></div>
                <button @click="startCall('audio')" class="text-lg">📞</button>
                <button @click="startCall('video')" class="text-lg">🎥</button>
            </header>
            <div id="msgScroll" class="flex flex-1 flex-col gap-1.5 overflow-y-auto p-3">
                <template x-for="m in messages" :key="m.id">
                    <div :class="m.is_mine ? 'items-end' : 'items-start'" class="flex flex-col">
                        <div :class="m.is_mine ? 'bg-brand text-white' : 'bg-white text-gray-900'"
                             class="max-w-[75%] rounded-2xl px-3 py-2 text-sm shadow-sm">
                            <template x-if="m.attachment_url"><img :src="m.attachment_url" class="mb-1 max-h-48 rounded-lg"></template>
                            <span x-show="m.body" x-text="m.body"></span>
                        </div>
                        <span class="px-1 text-[10px] text-gray-400" x-text="timeAgo(m.created_at)"></span>
                    </div>
                </template>
            </div>
            <div class="flex items-center gap-2 border-t border-gray-200 bg-white p-2">
                <label class="cursor-pointer text-xl">📎
                    <input type="file" accept="image/*" class="hidden" @change="sendAttachment($event)">
                </label>
                <input x-model="messageDraft" @keydown.enter="sendMessage()" placeholder="Message…"
                       class="flex-1 rounded-full bg-gray-100 px-4 py-2 text-sm outline-none">
                <button @click="sendMessage()" class="rounded-full bg-brand px-4 py-2 text-sm font-semibold text-white">Send</button>
            </div>
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

                    <div class="mt-4 space-y-2">
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
                        <button @click="openChatWith(viewedUser.id)" class="w-full rounded-xl bg-brand-light py-2.5 font-semibold text-brand">Message</button>
                        <button x-show="viewedUser.friendship_status==='blocked'" @click="unblock(viewedUser.id)"
                                class="w-full rounded-xl bg-red-100 py-2.5 font-semibold text-red-600">Unblock</button>
                        <button x-show="['none','friends','pending_outgoing','pending_incoming'].includes(viewedUser.friendship_status)"
                                @click="block(viewedUser.id)"
                                class="w-full rounded-xl border border-red-200 py-2.5 text-sm font-semibold text-red-500">Block</button>
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

    <!-- ===================== INCOMING CALL (modal) ===================== -->
    <template x-if="incomingCall">
        <div class="fixed inset-0 z-[55] flex flex-col items-center justify-center gap-6 bg-gray-900/95 text-white">
            <div class="h-28 w-28 overflow-hidden rounded-full bg-white/10">
                <img :src="(incomingCall.from.avatar_url) || placeholder(incomingCall.from.name)" class="h-full w-full object-cover">
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold" x-text="incomingCall.from.name"></div>
                <div class="text-sm text-white/70" x-text="'Incoming ' + incomingCall.call.type + ' call…'"></div>
            </div>
            <div class="flex gap-10">
                <button @click="declineCall()" class="flex h-16 w-16 items-center justify-center rounded-full bg-red-500 text-2xl">✕</button>
                <button @click="acceptCall()" class="flex h-16 w-16 items-center justify-center rounded-full bg-green-500 text-2xl">📞</button>
            </div>
        </div>
    </template>

    <!-- ===================== ACTIVE CALL (overlay) ===================== -->
    <template x-if="activeCall">
        <div class="fixed inset-0 z-50 flex flex-col bg-gray-900 text-white">
            <video id="remoteVideo" autoplay playsinline class="absolute inset-0 h-full w-full object-cover" :class="activeCall.type==='audio' ? 'hidden' : ''"></video>
            <video id="localVideo" autoplay playsinline muted class="absolute right-3 top-3 z-10 h-40 w-28 rounded-lg border border-white/20 object-cover" :class="activeCall.type==='audio' ? 'hidden' : ''"></video>

            <div class="relative z-10 mt-16 flex flex-col items-center gap-3" x-show="activeCall.type==='audio' || activeCall.state!=='connected'">
                <div class="h-28 w-28 overflow-hidden rounded-full bg-white/10">
                    <img :src="activeCall.peer.avatar_url || placeholder(activeCall.peer.name)" class="h-full w-full object-cover">
                </div>
                <div class="text-xl font-bold" x-text="activeCall.peer.name"></div>
                <div class="text-sm text-white/70" x-text="callStatusLabel()"></div>
            </div>

            <div class="absolute inset-x-0 bottom-10 z-10 flex justify-center gap-8">
                <button @click="toggleMute()" class="flex h-14 w-14 items-center justify-center rounded-full bg-white/15 text-xl" x-text="muted ? '🔇' : '🎤'"></button>
                <button @click="hangup()" class="flex h-16 w-16 items-center justify-center rounded-full bg-red-500 text-2xl">📵</button>
                <button x-show="activeCall.type==='video'" @click="toggleCam()" class="flex h-14 w-14 items-center justify-center rounded-full bg-white/15 text-xl">🎥</button>
            </div>
        </div>
    </template>

</div>
@endverbatim

<script>
    function nexora() {
        const META = (n) => (document.querySelector(`meta[name="${n}"]`) || {}).content || '';
        const API = META('api-url').replace(/\/$/, '');
        const APP_NAME = META('app-name');
        const REVERB = {
            key: META('reverb-key'),
            host: META('reverb-host'),
            port: parseInt(META('reverb-port') || '443', 10),
            path: META('reverb-path') || '/reverb-ws',
            scheme: META('reverb-scheme') || 'https',
        };
        const TOKEN_KEY = 'nexora_token';

        return {
            appName: APP_NAME,
            screen: 'splash',
            tab: 'feed',
            token: null,
            me: null,
            loading: false,
            error: '',
            fieldErrors: {},
            toast: '',
            // people
            searchQuery: '', searchResults: [], suggestions: [], friends: [], incoming: [], outgoing: [],
            viewedUser: null,
            // feed
            feed: [], composer: { content: '', visibility: 'friends', files: [], previews: [] },
            activePost: null, comments: [], commentDraft: '',
            // chat
            conversations: [], activeConv: null, messages: [], messageDraft: '', chatUnread: 0,
            // notifications
            notifications: [], unread: 0,
            // calls
            incomingCall: null, activeCall: null, muted: false,
            // realtime + pull-to-refresh
            pusher: null, rt: 'off', pull: 0, refreshing: false,
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
                if (status === 403 && json.code === 'email_not_verified') {
                    this.forms.otp = { email: (json.errors && json.errors.email && json.errors.email[0]) || this.forms.login.login, otp: '' };
                    this.showToast(json.message || 'Verify your email to continue.');
                    this.go('otp'); return;
                }
                if (!ok) { this.error = json.message || 'Login failed.'; return; }
                this.finishAuth(json);
            },
            async doRegister() {
                this.loading = true; this.fieldErrors = {}; this.error = '';
                const { ok, json } = await this.api('POST', '/auth/register', this.forms.register);
                this.loading = false;
                if (!ok) { this.fieldErrors = json.errors || { _: [json.message] }; return; }
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
            finishAuth(json) {
                this.token = json.data.access_token;
                localStorage.setItem(TOKEN_KEY, this.token);
                this.me = json.data.user;
                this.screen = 'main'; this.bootMain();
            },
            async logout() {
                this.teardownRealtime();
                await this.api('POST', '/auth/logout').catch(() => {});
                this.forceLogout();
            },
            forceLogout() {
                localStorage.removeItem(TOKEN_KEY);
                this.token = null; this.me = null;
                this.screen = 'login';
                this.forms.login = { login: '', password: '' };
            },

            // ---- Bootstrap ----
            bootMain() {
                this.tab = 'feed';
                this.loadFeed();
                this.loadSuggestions();
                this.loadFriends();
                this.loadRequests();
                this.loadConversations();
                this.loadNotifications();
                this.setupRealtime();
            },
            async loadMe() {
                const { ok, json } = await this.api('GET', '/auth/me');
                if (ok) { this.me = json.data; return true; }
                return false;
            },

            // ---- Feed / Posts ----
            async loadFeed() {
                const { ok, json } = await this.api('GET', '/posts');
                if (ok) this.feed = json.data || [];
            },
            pickComposerImages(e) {
                for (const f of e.target.files) {
                    this.composer.files.push(f);
                    this.composer.previews.push(URL.createObjectURL(f));
                }
                e.target.value = '';
            },
            removeComposerImage(i) { this.composer.files.splice(i, 1); this.composer.previews.splice(i, 1); },
            async submitPost() {
                if (!this.composer.content.trim() && !this.composer.files.length) { this.showToast('Write something first'); return; }
                this.loading = true;
                const fd = new FormData();
                if (this.composer.content.trim()) fd.append('content', this.composer.content.trim());
                fd.append('visibility', this.composer.visibility);
                this.composer.files.forEach(f => fd.append('media[]', f));
                const { ok, json } = await this.api('POST', '/posts', fd, true);
                this.loading = false;
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.feed.unshift(json.data);
                this.composer = { content: '', visibility: this.composer.visibility, files: [], previews: [] };
                this.showToast('Posted!');
            },
            async deletePost(post) {
                await this.api('DELETE', '/posts/' + post.id);
                this.feed = this.feed.filter(p => p.id !== post.id);
                this.showToast('Post deleted');
            },
            async toggleLike(post) {
                if (post.my_reaction) {
                    const { ok, json } = await this.api('DELETE', '/posts/' + post.id + '/reactions');
                    if (ok) { post.my_reaction = null; post.reactions_count = json.data.reactions_count; }
                } else {
                    const { ok, json } = await this.api('POST', '/posts/' + post.id + '/reactions', { type: 'like' });
                    if (ok) { post.my_reaction = json.data.type; post.reactions_count = json.data.reactions_count; }
                }
            },
            async openComments(post) {
                this.activePost = post; this.comments = []; this.commentDraft = '';
                this.screen = 'comments';
                const { ok, json } = await this.api('GET', '/posts/' + post.id + '/comments');
                if (ok) this.comments = (json.data || []).slice().reverse();
            },
            closeComments() { this.activePost = null; this.screen = 'main'; },
            async submitComment() {
                const body = this.commentDraft.trim();
                if (!body) return;
                this.commentDraft = '';
                const { ok, json } = await this.api('POST', '/posts/' + this.activePost.id + '/comments', { content: body });
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.comments.push(json.data);
                this.activePost.comments_count++;
                const fp = this.feed.find(p => p.id === this.activePost.id);
                if (fp) fp.comments_count++;
            },

            // ---- People / Friends ----
            async loadSuggestions() { const { ok, json } = await this.api('GET', '/friends/suggestions'); if (ok) this.suggestions = json.data || []; },
            async doSearch() {
                if (!this.searchQuery.trim()) { this.searchResults = []; return; }
                const { ok, json } = await this.api('GET', '/users/search?q=' + encodeURIComponent(this.searchQuery));
                if (ok) this.searchResults = json.data || [];
            },
            async loadFriends() { const { ok, json } = await this.api('GET', '/friends'); if (ok) this.friends = json.data || []; },
            async loadRequests() {
                const inc = await this.api('GET', '/friends/requests/incoming');
                if (inc.ok) this.incoming = inc.json.data || [];
                const out = await this.api('GET', '/friends/requests/outgoing');
                if (out.ok) this.outgoing = out.json.data || [];
            },
            async sendRequest(id) {
                const { ok, json } = await this.api('POST', '/friends/requests/' + id);
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.showToast('Friend request sent');
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'pending_outgoing';
                // Keep the person in "People you may know"; the button flips to
                // Cancel once outgoing requests reload (isRequested() turns true).
                await this.loadRequests();
            },
            // True when there's a pending outgoing request to this user.
            isRequested(id) {
                return this.outgoing.some(r => r.user && r.user.id === id);
            },
            async accept(id) {
                const { ok, json } = await this.api('POST', '/friends/requests/' + id + '/accept');
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.showToast('You are now friends');
                if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'friends';
                this.loadRequests(); this.loadFriends();
            },
            async decline(id) { await this.api('POST', '/friends/requests/' + id + '/decline'); if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none'; this.loadRequests(); },
            async cancel(id) { await this.api('DELETE', '/friends/requests/' + id); if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none'; this.loadRequests(); },
            async unfriend(id) { await this.api('DELETE', '/friends/' + id); this.showToast('Removed from friends'); if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none'; this.loadFriends(); },
            async block(id) { await this.api('POST', '/friends/' + id + '/block'); this.showToast('User blocked'); if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'blocked'; this.loadFriends(); this.loadRequests(); },
            async unblock(id) { await this.api('DELETE', '/friends/' + id + '/block'); if (this.viewedUser && this.viewedUser.id === id) this.viewedUser.friendship_status = 'none'; this.showToast('User unblocked'); },

            // ---- Profiles ----
            async viewUser(username) {
                const { ok, json } = await this.api('GET', '/users/' + username);
                if (!ok) { this.showToast('Could not load profile'); return; }
                this.viewedUser = json.data; this.screen = 'userProfile';
            },
            closeUser() { this.viewedUser = null; this.screen = 'main'; },
            openEdit() {
                this.forms.edit = { name: this.me.name || '', bio: this.me.profile.bio || '', location: this.me.profile.location || '', work: this.me.profile.work || '', website: this.me.profile.website || '' };
                this.screen = 'editProfile';
            },
            async saveProfile() {
                this.loading = true;
                const { ok, json } = await this.api('PATCH', '/profile', this.forms.edit);
                this.loading = false;
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.me = json.data; this.showToast('Profile updated'); this.screen = 'main'; this.tab = 'profile';
            },
            async uploadAvatar(event) {
                const file = event.target.files[0]; if (!file) return;
                const fd = new FormData(); fd.append('image', file);
                const { ok, json } = await this.api('POST', '/profile/avatar', fd, true);
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.me = json.data; this.showToast('Photo updated');
            },
            async changePassword() {
                this.loading = true;
                const { ok, json } = await this.api('POST', '/auth/change-password', this.forms.password);
                this.loading = false;
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.forms.password = { current_password: '', password: '', password_confirmation: '' };
                this.showToast('Password changed');
            },

            // ---- Chat ----
            async loadConversations() {
                const { ok, json } = await this.api('GET', '/conversations');
                if (ok) { this.conversations = json.data || []; this.recomputeChatUnread(); }
            },
            recomputeChatUnread() { this.chatUnread = this.conversations.reduce((s, c) => s + (c.unread_count || 0), 0); },
            async openChatWith(userId) {
                const { ok, json } = await this.api('POST', '/conversations', { user_id: userId });
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.closeUser();
                this.openConversation(json.data);
            },
            async openConversation(conv) {
                this.activeConv = conv; this.messages = []; this.screen = 'conversation';
                const { ok, json } = await this.api('GET', '/conversations/' + conv.id + '/messages');
                if (ok) this.messages = (json.data || []).slice().reverse();
                conv.unread_count = 0; this.recomputeChatUnread();
                this.subscribeConversation(conv.id);
                this.scrollMessages();
            },
            closeConversation() {
                try { if (this.pusher && this.activeConv) this.pusher.unsubscribe('private-conversation.' + this.activeConv.id); } catch (e) {}
                this.activeConv = null; this.screen = 'main';
                this.loadConversations();
            },
            async sendMessage() {
                const body = this.messageDraft.trim(); if (!body) return;
                this.messageDraft = '';
                const { ok, json } = await this.api('POST', '/conversations/' + this.activeConv.id + '/messages', { body });
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.appendMessage(json.data);
            },
            async sendAttachment(e) {
                const file = e.target.files[0]; if (!file) return;
                const fd = new FormData(); fd.append('attachment', file);
                const { ok, json } = await this.api('POST', '/conversations/' + this.activeConv.id + '/messages', fd, true);
                e.target.value = '';
                if (!ok) { this.showToast(this.firstError(json)); return; }
                this.appendMessage(json.data);
            },
            appendMessage(m) {
                if (this.messages.find(x => x.id === m.id)) return;
                this.messages.push(m); this.scrollMessages();
            },
            scrollMessages() {
                this.$nextTick(() => { const el = document.getElementById('msgScroll'); if (el) el.scrollTop = el.scrollHeight; });
            },

            // ---- Notifications ----
            async loadNotifications() {
                const { ok, json } = await this.api('GET', '/notifications');
                if (ok) this.notifications = json.data || [];
                const u = await this.api('GET', '/notifications/unread-count');
                if (u.ok) this.unread = u.json.data.unread || 0;
            },
            async markAllRead() {
                await this.api('POST', '/notifications/read-all');
                this.notifications.forEach(n => n.read = true); this.unread = 0;
            },
            notifText(n) {
                switch (n.type) {
                    case 'friend_request': return 'sent you a friend request';
                    case 'friend_accepted': return 'accepted your friend request';
                    case 'post_comment': return 'commented on your post';
                    case 'reaction': return 'reacted to your post';
                    case 'message': return 'sent you a message';
                    default: return 'did something';
                }
            },

            // ---- Realtime (raw pusher-js → Reverb) ----
            setupRealtime() {
                if (this.pusher || !window.Pusher || !this.me || !REVERB.key) return;
                try {
                    this.pusher = new window.Pusher(REVERB.key, {
                        wsHost: REVERB.host,
                        wsPort: REVERB.port,
                        wssPort: REVERB.port,
                        wsPath: REVERB.path,
                        forceTLS: REVERB.scheme === 'https',
                        enabledTransports: ['ws', 'wss'],
                        cluster: 'mt1', // ignored when wsHost is set, but pusher-js requires a value
                        enableStats: false,
                        channelAuthorization: {
                            endpoint: API + '/broadcasting/auth',
                            headers: { Authorization: 'Bearer ' + this.token },
                        },
                    });
                    const c = this.pusher.connection;
                    c.bind('connected', () => { this.rt = 'on'; });
                    c.bind('connecting', () => { this.rt = '...'; });
                    c.bind('unavailable', () => { this.rt = 'off'; });
                    c.bind('failed', () => { this.rt = 'off'; });
                    c.bind('error', () => { this.rt = 'off'; });

                    const me = this.pusher.subscribe('private-user.' + this.me.id);
                    me.bind('notification.created', (d) => this.onNotification(d.notification));
                    me.bind('call.signal', (d) => this.onCallSignal(d));

                    // Public feed channel: live reaction/comment counts for any post.
                    const feed = this.pusher.subscribe('posts');
                    feed.bind('post.stats', (d) => this.onPostStats(d));
                } catch (err) { this.rt = 'off'; console.warn('Realtime init failed', err); }
            },
            teardownRealtime() {
                try { if (this.pusher) this.pusher.disconnect(); } catch (e) {}
                this.pusher = null; this.rt = 'off';
            },
            subscribeConversation(id) {
                if (!this.pusher) return;
                const ch = this.pusher.subscribe('private-conversation.' + id);
                ch.bind('message.sent', (d) => {
                    const m = d.message;
                    if (!this.me || !m.sender || m.sender.id !== this.me.id) {
                        this.appendMessage({ ...m, is_mine: false });
                    }
                });
            },
            onNotification(n) {
                this.notifications.unshift({ ...n, read: false });
                this.unread++;
                if (n.type === 'message') { this.loadConversations(); }
                if (n.type === 'friend_request' || n.type === 'friend_accepted') {
                    this.loadRequests(); this.loadFriends(); this.loadSuggestions();
                }
                this.showToast((n.actor ? n.actor.name : 'Someone') + ' ' + this.notifText(n));
            },
            // Live reaction/comment counts pushed on the public "posts" channel.
            onPostStats(d) {
                const p = this.feed.find(x => x.id === d.post_id);
                if (p) { p.reactions_count = d.reactions_count; p.comments_count = d.comments_count; }
                if (this.activePost && this.activePost.id === d.post_id) {
                    this.activePost.reactions_count = d.reactions_count;
                    this.activePost.comments_count = d.comments_count;
                    this.api('GET', '/posts/' + d.post_id + '/comments').then(({ ok, json }) => {
                        if (ok) this.comments = (json.data || []).slice().reverse();
                    });
                }
            },

            // ---- Calls (WebRTC) ----
            async iceConfig() {
                if (this._ice) return this._ice;
                const { ok, json } = await this.api('GET', '/calls/ice-servers');
                this._ice = { iceServers: ok ? json.data.ice_servers : [{ urls: 'stun:stun.l.google.com:19302' }] };
                return this._ice;
            },
            async startCall(type) {
                if (!this.activeConv || !this.activeConv.other) { this.showToast('Open a chat first'); return; }
                const peer = this.activeConv.other;
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: type === 'video' });
                    this.activeCall = { type, peer, state: 'calling', id: null, role: 'caller' };
                    await this.setupPeer(stream);
                    const offer = await this.pc.createOffer();
                    await this.pc.setLocalDescription(offer);
                    const { ok, json } = await this.api('POST', '/calls', { callee_id: peer.id, type, sdp: { type: offer.type, sdp: offer.sdp } });
                    if (!ok) { this.showToast(this.firstError(json)); this.cleanupCall(); return; }
                    this.activeCall.id = json.data.id;
                } catch (e) { this.showToast('Camera/mic unavailable'); this.cleanupCall(); }
            },
            async acceptCall() {
                const inc = this.incomingCall; this.incomingCall = null;
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: inc.call.type === 'video' });
                    this.activeCall = { type: inc.call.type, peer: inc.from, state: 'connecting', id: inc.call.id, role: 'callee' };
                    await this.setupPeer(stream);
                    await this.pc.setRemoteDescription(new RTCSessionDescription(inc.sdp));
                    const answer = await this.pc.createAnswer();
                    await this.pc.setLocalDescription(answer);
                    await this.api('POST', '/calls/' + inc.call.id + '/answer', { sdp: { type: answer.type, sdp: answer.sdp } });
                    this.flushCandidates();
                } catch (e) { this.showToast('Camera/mic unavailable'); this.cleanupCall(); }
            },
            async declineCall() {
                if (this.incomingCall) await this.api('POST', '/calls/' + this.incomingCall.call.id + '/decline');
                this.incomingCall = null;
            },
            async setupPeer(stream) {
                const cfg = await this.iceConfig();
                this.localStream = stream;
                this.pc = new RTCPeerConnection(cfg);
                this.pendingCandidates = [];
                stream.getTracks().forEach(t => this.pc.addTrack(t, stream));
                this.pc.onicecandidate = (ev) => {
                    if (ev.candidate && this.activeCall && this.activeCall.id) {
                        this.api('POST', '/calls/' + this.activeCall.id + '/candidate', { candidate: ev.candidate.toJSON ? ev.candidate.toJSON() : ev.candidate });
                    }
                };
                this.pc.ontrack = (ev) => {
                    if (this.activeCall) this.activeCall.state = 'connected';
                    this.$nextTick(() => { const v = document.getElementById('remoteVideo'); if (v) v.srcObject = ev.streams[0]; });
                };
                this.$nextTick(() => { const v = document.getElementById('localVideo'); if (v) v.srcObject = stream; });
            },
            flushCandidates() {
                (this.pendingCandidates || []).forEach(c => this.pc.addIceCandidate(new RTCIceCandidate(c)).catch(() => {}));
                this.pendingCandidates = [];
            },
            async onCallSignal(e) {
                if (e.signal === 'incoming') {
                    if (this.activeCall || this.incomingCall) { return; } // busy
                    this.incomingCall = { call: e.call, from: e.from, sdp: e.sdp };
                    return;
                }
                if (!this.activeCall) return;
                if (e.signal === 'answer') {
                    await this.pc.setRemoteDescription(new RTCSessionDescription(e.sdp));
                    this.activeCall.state = 'connected'; this.flushCandidates();
                } else if (e.signal === 'candidate') {
                    if (this.pc && this.pc.remoteDescription) { this.pc.addIceCandidate(new RTCIceCandidate(e.candidate)).catch(() => {}); }
                    else { (this.pendingCandidates = this.pendingCandidates || []).push(e.candidate); }
                } else if (['ended', 'declined', 'canceled'].includes(e.signal)) {
                    this.showToast('Call ' + e.signal);
                    this.cleanupCall();
                }
            },
            async hangup() {
                if (this.activeCall && this.activeCall.id) await this.api('POST', '/calls/' + this.activeCall.id + '/hangup').catch(() => {});
                this.cleanupCall();
            },
            cleanupCall() {
                try { if (this.pc) this.pc.close(); } catch (e) {}
                try { if (this.localStream) this.localStream.getTracks().forEach(t => t.stop()); } catch (e) {}
                this.pc = null; this.localStream = null; this.activeCall = null; this.muted = false;
            },
            toggleMute() {
                this.muted = !this.muted;
                if (this.localStream) this.localStream.getAudioTracks().forEach(t => t.enabled = !this.muted);
            },
            toggleCam() {
                if (this.localStream) this.localStream.getVideoTracks().forEach(t => t.enabled = !t.enabled);
            },
            callStatusLabel() {
                if (!this.activeCall) return '';
                return { calling: 'Calling…', connecting: 'Connecting…', connected: 'Connected' }[this.activeCall.state] || '';
            },

            // ---- UI helpers ----
            setTab(t) {
                this.tab = t;
                if (t === 'feed') this.loadFeed();
                if (t === 'people') { this.loadFriends(); this.loadRequests(); this.loadSuggestions(); }
                if (t === 'chat') this.loadConversations();
                if (t === 'alerts') this.loadNotifications();
                if (t === 'profile') this.loadMe();
            },
            go(s) {
                if (s === 'main') { this.screen = 'main'; return; }
                this.error = ''; this.fieldErrors = {};
                this.screen = s;
            },

            // ---- Pull to refresh ----
            ptrStart(e) {
                this._ptrEl = e.currentTarget;
                this._ptrY = e.touches[0].clientY;
                this._ptrOk = this._ptrEl.scrollTop <= 0;
            },
            ptrMove(e) {
                if (!this._ptrOk || this.refreshing) return;
                const dy = e.touches[0].clientY - this._ptrY;
                this.pull = dy > 0 ? Math.min(dy * 0.5, 90) : 0;
            },
            async ptrEnd() {
                const trigger = this.pull > 70;
                this.pull = 0;
                if (trigger) await this.refreshCurrent();
            },
            async refreshCurrent() {
                this.refreshing = true;
                try { await this.refreshTab(this.tab); this.showToast('Updated'); }
                finally { this.refreshing = false; }
            },
            async refreshTab(t) {
                if (t === 'feed') await this.loadFeed();
                else if (t === 'people') { await this.loadFriends(); await this.loadRequests(); await this.loadSuggestions(); }
                else if (t === 'chat') await this.loadConversations();
                else if (t === 'alerts') await this.loadNotifications();
                else if (t === 'profile') await this.loadMe();
            },
            placeholder(name) {
                const initials = (name || '?').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
                return 'data:image/svg+xml;utf8,' + encodeURIComponent(
                    `<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#dbeafe"/><text x="50" y="58" font-size="38" text-anchor="middle" fill="#2563eb" font-family="Arial">${initials}</text></svg>`
                );
            },
            timeAgo(iso) {
                if (!iso) return '';
                const s = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
                if (s < 60) return 'now';
                if (s < 3600) return Math.floor(s / 60) + 'm';
                if (s < 86400) return Math.floor(s / 3600) + 'h';
                return Math.floor(s / 86400) + 'd';
            },
            firstError(json) {
                if (json.errors) { const k = Object.keys(json.errors)[0]; return json.errors[k][0]; }
                return json.message || 'Something went wrong.';
            },
            showToast(msg) { this.toast = msg; clearTimeout(this._t); this._t = setTimeout(() => this.toast = '', 2400); },
        };
    }
</script>
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</body>
</html>
