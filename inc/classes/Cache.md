# Caching Best Practices

Cache is something that requires careful consideration. When done improperly it can result in bottlenecks, stale data, or unintended collisons. So we want to make sure that when we introduce caching on top of exisitng cache layers we are doing so deliberately and with the right approach.

## Managed Hosts
One factor to consider is that enterprise level hosts have several layers of caching built in. These include, but aren't limited to:
1. **Page Caching**: For guest visitors to the site, and on some hosts even for those logged in, the entire page is cached at the HTML level in short intervals. This is then intelligently invalidated on page or post updates. But there _are times_ when the cache won't be purged correctly for pages, if for instance data is being edited outside of the context of the editor.
2. **Object Level Caching**:
3. Reverse Proxy Caching

**Links to Host specific info**
[ Include links to host doc pages ]

## Cache Invalidation

## Third Party Requests

