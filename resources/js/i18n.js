import { usePage } from '@inertiajs/vue3';

/**
 * Translate a dot-notation key against the translations shared by the backend
 * (loaded from lang/<locale>/ui.php via Inertia). Mirrors Laravel's __() helper.
 *
 * @param {string} key  e.g. 'auth.login_title'
 * @param {Record<string, string|number>} [replacements]  e.g. { name: 'Ana' } -> replaces :name
 * @returns {string}
 */
export function __(key, replacements = {}) {
    const translations = usePage().props.translations ?? {};

    let value = key
        .split('.')
        .reduce((carry, segment) => carry?.[segment], translations);

    if (typeof value !== 'string') {
        return key;
    }

    for (const [token, replacement] of Object.entries(replacements)) {
        value = value.replace(`:${token}`, String(replacement));
    }

    return value;
}
