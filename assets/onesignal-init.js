window.OneSignalDeferred = window.OneSignalDeferred || [];

OneSignalDeferred.push(async function (OneSignal) {
    const appId = window.__ONESIGNAL_APP_ID__;
    if (!appId) return;

    await OneSignal.init({
        appId: appId,
        notifyButton: { enable: false },
        allowLocalhostAsSecureOrigin: true,
        serviceWorkerParam: { scope: '/' },
        serviceWorkerPath: '/OneSignalSDKWorker.js',
    });

    const btn = document.getElementById('nx-notif-btn');
    if (!btn) return;

    // Hide button if already subscribed
    if (OneSignal.User.PushSubscription.optedIn) {
        btn.style.display = 'none';
        return;
    }

    btn.style.display = 'inline-flex';

    btn.addEventListener('click', async () => {
        await OneSignal.User.PushSubscription.optIn();
        btn.style.display = 'none';
    });
});
