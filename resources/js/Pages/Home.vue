<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50">
        <div class="text-center max-w-lg px-6">
            <h1 class="text-5xl font-bold text-gray-900 mb-3">RunYx</h1>
            <p class="text-lg text-gray-500 mb-10">Centralisez vos données Strava et Garmin en un seul endroit.</p>

            <a
                href="/auth/strava"
                class="inline-flex items-center gap-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-md"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066l-2.084 4.116z"/>
                    <path d="M11.979 0L7.545 8.817h3.065l1.369-2.708 1.37 2.708h3.065L11.979 0z"/>
                </svg>
                Se connecter avec Strava
            </a>

            <p v-if="errors?.strava" class="mt-6 text-red-500 text-sm">
                {{ errors.strava[0] }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const errors = computed(() => page.props.errors)
</script>
