export class State {
  constructor(initial = {}) {
    this.state = { ...initial };
    this.listeners = new Set();
  }

  setState(patchOrUpdater) {
    const prev = this.state;
    const patch =
      typeof patchOrUpdater === 'function'
        ? patchOrUpdater({ ...prev })
        : patchOrUpdater || {};
    this.state = Object.assign({}, this.state, patch);
    this.listeners.forEach((fn) => fn(this.state, prev, patch));
  }

  subscribe(fn) {
    this.listeners.add(fn);
  }

  unsubscribe(fn) {
    this.listeners.delete(fn);
  }
}
