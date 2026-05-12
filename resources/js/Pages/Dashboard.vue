<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <span class="text-xl font-bold text-gray-900">RunYx</span>
            <form method="POST" action="/logout" @submit.prevent="logout">
                <button
                    type="submit"
                    class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                >
                    Déconnexion
                </button>
            </form>
        </nav>

        <main class="max-w-2xl mx-auto px-6 py-12">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex items-center gap-6">
                <img
                    v-if="user.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="w-20 h-20 rounded-full object-cover ring-2 ring-orange-500"
                />
                <div v-else class="w-20 h-20 rounded-full bg-orange-100 flex items-center justify-center text-orange-500 text-2xl font-bold">
                    {{ user.name?.charAt(0) }}
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
                    <p v-if="user.email" class="text-gray-500 mt-1">{{ user.email }}</p>
                    <span class="inline-flex items-center gap-1.5 mt-2 text-sm text-orange-600 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                            <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066l-2.084 4.116z"/>
                            <path d="M11.979 0L7.545 8.817h3.065l1.369-2.708 1.37 2.708h3.065L11.979 0z"/>
                        </svg>
                        Connecté via Strava
                    </span>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    user: Object,
})

function logout() {
    router.post('/logout')
}
</script>
