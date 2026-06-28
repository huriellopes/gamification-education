/**
 * Utils for masking input values.
 */

export function maskPhone(value) {
    if (!value) return '';
    const clean = value.replace(/\D/g, '');
    if (clean.length <= 10) {
        return clean.replace(/^(\d{2})(\d{4})(\d{0,4})$/, (_, g1, g2, g3) => {
            return `(${g1}) ${g2}` + (g3 ? `-${g3}` : '');
        });
    }
    return clean.slice(0, 11).replace(/^(\d{2})(\d{5})(\d{0,4})$/, (_, g1, g2, g3) => {
        return `(${g1}) ${g2}` + (g3 ? `-${g3}` : '');
    });
}

export function maskCnpj(value) {
    if (!value) return '';
    const clean = value.replace(/\D/g, '').slice(0, 14);
    return clean.replace(/^(\d{2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})$/, (_, g1, g2, g3, g4, g5) => {
        let res = g1;
        if (g2) res += `.${g2}`;
        if (g3) res += `.${g3}`;
        if (g4) res += `/${g4}`;
        if (g5) res += `-${g5}`;
        return res;
    });
}

export function maskCpf(value) {
    if (!value) return '';
    const clean = value.replace(/\D/g, '').slice(0, 11);
    return clean.replace(/^(\d{3})(\d{0,3})(\d{0,3})(\d{0,2})$/, (_, g1, g2, g3, g4) => {
        let res = g1;
        if (g2) res += `.${g2}`;
        if (g3) res += `.${g3}`;
        if (g4) res += `-${g4}`;
        return res;
    });
}

export function maskCep(value) {
    if (!value) return '';
    const clean = value.replace(/\D/g, '').slice(0, 8);
    if (clean.length <= 5) {
        return clean;
    }
    return clean.replace(/^(\d{5})(\d{0,3})$/, '$1-$2');
}

export function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}
