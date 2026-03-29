import * as PusherPushNotifications from '@pusher/push-notifications-web';

/**
 * Pusher Beams (Web Push) — device interests.
 * @see https://pusher.com/docs/beams/reference/web#npm-yarn
 */
export async function initPusherBeams() {
    if (typeof window === 'undefined' || !('serviceWorker' in navigator)) {
        return;
    }

    const instanceId = import.meta.env.VITE_PUSHER_BEAMS_INSTANCE_ID;
    if (!instanceId) {
        return;
    }

    const defaultInterest = import.meta.env.VITE_PUSHER_BEAMS_DEFAULT_INTEREST ?? 'hello';

    try {
        let registration = await navigator.serviceWorker.getRegistration();
        if (!registration) {
            registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        }

        const beamsClient = new PusherPushNotifications.Client({
            instanceId,
            serviceWorkerRegistration: registration,
        });

        await beamsClient.start();
        await beamsClient.addDeviceInterest(defaultInterest);

        window.PusherBeams = beamsClient;
        console.log('Pusher Beams: subscribed to interest', defaultInterest);
    } catch (e) {
        console.warn('Pusher Beams init failed:', e);
    }
}
