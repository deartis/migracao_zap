declare module 'event-source-polyfill' {
    export class EventSourcePolyfill extends EventSource {
        constructor(url: string, eventSourceInitDict?: EventSourcePolyfillInit);
    }
    export interface EventSourcePolyfillInit extends EventSourceInit {
        headers?: Record<string, string>;
    }
    export type Event = globalThis.Event;
    export type MessageEvent = globalThis.MessageEvent;
}