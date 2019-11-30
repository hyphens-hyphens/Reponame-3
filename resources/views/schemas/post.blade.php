<?php /** @var \T2G\Common\Models\Post $post */ ?>
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "Article",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ url()->current() }}"
  },
  "headline": "{{ $post->title }}",
  "description": "{{ $post->getDescription() ?? '' }}",
  "image": {
    "@type": "ImageObject",
    "url": "{{ $post->getImage() }}",
    "width": 720,
    "height": 480
  },
  "datePublished": "{{ $post->created_at->format('c') }}",
  "dateModified": "{{ $post->updated_at->format('c') }}",
  "author": {
    "@type": "Organization",
    "name": "{{ config('app.name') }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{ config('app.name') }}",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('images/logo.png') }}"
    }
  }
}
</script>
