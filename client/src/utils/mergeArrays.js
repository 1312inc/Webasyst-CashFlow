export function mergeArrays (arr1, arr2) {
  const map = new Map(arr1.map(item => [item.id, item]))
  arr2.forEach(item => {
    if (!map.has(item.id)) map.set(item.id, item)
  })
  return Array.from(map.values())
}
