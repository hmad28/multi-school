import { onBeforeUnmount, watch, type WatchSource } from 'vue';

export const useDebouncedSearch = (sources: WatchSource | WatchSource[], callback: () => void, delay = 300) => {
    let timeout: ReturnType<typeof setTimeout> | undefined;

    const stop = watch(
        sources,
        () => {
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(callback, delay);
        },
    );

    onBeforeUnmount(() => {
        stop();
        if (timeout) clearTimeout(timeout);
    });
};
