<script type="application/ld+json">
{
    "@context" : "http://schema.org",
    "@type" : "WebSite",
    "name" : "{{ config('app.name') }}",
    "url": "https://{{ array_first(config('t2g_common.site.domains')) }}",
    "about": "{{ config('t2g_common.site.seo.meta_description') }}"
}
</script>
