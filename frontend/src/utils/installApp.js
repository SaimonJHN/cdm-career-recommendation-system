import { reactive } from 'vue';

const state = reactive({
  deferredPrompt: null,
  installed: false,
  ios: false,
});

const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches
  || window.navigator.standalone === true;

export const initializeInstallApp = () => {
  state.installed = isStandalone();
  state.ios = /iphone|ipad|ipod/i.test(navigator.userAgent);

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    state.deferredPrompt = event;
  });

  window.addEventListener('appinstalled', () => {
    state.installed = true;
    state.deferredPrompt = null;
  });
};

export const useInstallApp = () => {
  const install = async () => {
    if (!state.deferredPrompt) return false;
    state.deferredPrompt.prompt();
    const choice = await state.deferredPrompt.userChoice;
    state.deferredPrompt = null;
    return choice.outcome === 'accepted';
  };

  return { state, install };
};
