/**
 * Get the first item that pass the test
 * by second argument function
 *
 * @param {Array} list
 * @param {Function} f
 * @return {*}
 */
export const find = (list, f) => list.filter(f)[0]

/**
 * Deep copy the given object considering circular structure.
 * This function caches all nested objects and its copies.
 * If it detects circular structure, use cached copy to avoid infinite loop.
 *
 * @param {*} obj
 * @param {Array<Object>} cache
 * @return {*}
 */
export function deepCopy (obj, cache = []) {
    if (obj === null || typeof obj !== 'object') return obj

    const hit = find(cache, c => c.original === obj)
    if (hit) return hit.copy

    const copy = Array.isArray(obj) ? [] : {}

    cache.push({
        original: obj,
        copy
    })

    for(var key in obj) copy[key] = deepCopy(obj[key], cache)

    return copy
}

export const isObject = obj => obj !== null && typeof obj === 'object'
export const isPromise = val => val && typeof val.then === 'function'

export function assert (condition, msg) {
    if (!condition) throw new Error(`[vuex] ${msg}`)
}