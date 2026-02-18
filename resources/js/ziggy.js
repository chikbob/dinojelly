import { route } from 'ziggy-js';

export default {
    ...window.Ziggy,
    location: new URL(window.Ziggy.url),
};

export function useRoutes() {
    return route;
}
