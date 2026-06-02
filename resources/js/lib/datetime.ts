type DateValue = string | Date | null | undefined;

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const shortDateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const dateTimeFormatter = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const parseDate = (value: DateValue): Date | null => {
    if (!value) return null;
    if (value instanceof Date) return Number.isNaN(value.getTime()) ? null : value;

    const dateOnly = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (dateOnly) {
        return new Date(Number(dateOnly[1]), Number(dateOnly[2]) - 1, Number(dateOnly[3]));
    }

    const parsed = new Date(value);

    return Number.isNaN(parsed.getTime()) ? null : parsed;
};

export const formatDate = (value: DateValue): string => {
    const date = parseDate(value);

    return date ? dateFormatter.format(date) : '-';
};

export const formatShortDate = (value: DateValue): string => {
    const date = parseDate(value);

    return date ? shortDateFormatter.format(date) : '-';
};

export const formatDateTime = (value: DateValue): string => {
    const date = parseDate(value);

    return date ? `${dateTimeFormatter.format(date)} WIB` : '-';
};

export const formatDateRange = (from: DateValue, to: DateValue): string => {
    const start = formatShortDate(from);
    const end = formatShortDate(to);

    if (start === '-' && end === '-') return '-';
    if (start === end) return start;

    return `${start} - ${end}`;
};
