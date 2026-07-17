/**
 * Minimal pub/sub store:
 * shallow-merges patches into state and notifies subscribers.
 */
export class State {
  constructor(initial = {}) {
    this.state = { ...initial };
    this.listeners = new Set();
  }

  /**
   * @param {object | ((prev: object) => object)} patchOrUpdater
   *   Either a partial state object to merge in, or a function that receives
   *   the previous state and returns the partial object to merge in.
   */
  setState(patchOrUpdater) {
    const prev = this.state;
    const patch =
      typeof patchOrUpdater === 'function'
        ? patchOrUpdater({ ...prev })
        : patchOrUpdater || {};
    this.state = Object.assign({}, this.state, patch);
    this.listeners.forEach((fn) => fn(this.state, prev, patch));
  }

  /**
   * @param {Function} fn Called on every setState as fn(state, prev, patch).
   */
  subscribe(fn) {
    this.listeners.add(fn);
  }

  /**
   * @param {Function} fn The exact function reference passed to subscribe().
   */
  unsubscribe(fn) {
    this.listeners.delete(fn);
  }
}
