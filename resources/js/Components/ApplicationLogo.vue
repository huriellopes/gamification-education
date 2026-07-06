<script setup>
/**
 * GamificaEdu brand mark.
 *
 * Concept: a graduation cap (education) sitting on a rounded "achievement
 * badge" (gamification), with an amber tassel bead standing in for the XP /
 * reward spark. Colors follow the brand system: indigo→violet gradient for the
 * badge and amber for the reward accent.
 *
 * The gradient id is suffixed with `uid` so multiple instances on the same page
 * never collide (an SVG <defs> id must be unique in the document).
 */
import { useId } from 'vue';

// `badge` renders the full gradient badge (default, for standalone marks like
// the auth pages). `glyph` renders only the cap on a transparent background,
// tinted with the current text color — handy inside an already-colored box.
defineProps({
    variant: {
        type: String,
        default: 'badge',
        validator: (v) => ['badge', 'glyph'].includes(v),
    },
});

const uid = useId();
</script>

<template>
    <svg
        viewBox="0 0 64 64"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        aria-label="GamificaEdu"
    >
        <defs>
            <linearGradient
                :id="`gedu-badge-${uid}`"
                x1="8"
                y1="6"
                x2="56"
                y2="58"
                gradientUnits="userSpaceOnUse"
            >
                <stop stop-color="#818cf8" />
                <stop offset="0.55" stop-color="#6366f1" />
                <stop offset="1" stop-color="#7c3aed" />
            </linearGradient>
        </defs>

        <!-- Achievement badge -->
        <rect
            v-if="variant === 'badge'"
            x="4"
            y="4"
            width="56"
            height="56"
            rx="16"
            :fill="`url(#gedu-badge-${uid})`"
        />

        <!-- Graduation cap: mortarboard -->
        <path
            d="M32 18 54 27 32 36 10 27 32 18Z"
            :fill="variant === 'badge' ? '#ffffff' : 'currentColor'"
            stroke-linejoin="round"
        />
        <!-- Graduation cap: base / head cover -->
        <path
            d="M21 30.5V38c0 4.5 5 7 11 7s11-2.5 11-7v-7.5L32 35 21 30.5Z"
            :fill="variant === 'badge' ? '#ffffff' : 'currentColor'"
            fill-opacity="0.92"
            stroke-linejoin="round"
        />
        <!-- Tassel cord -->
        <path
            d="M54 27v12"
            :stroke="variant === 'badge' ? '#fbbf24' : 'currentColor'"
            stroke-width="2.4"
            stroke-linecap="round"
        />
        <!-- Tassel bead: the XP / reward spark -->
        <circle
            cx="54"
            cy="42"
            r="3"
            :fill="variant === 'badge' ? '#fbbf24' : 'currentColor'"
        />
    </svg>
</template>
