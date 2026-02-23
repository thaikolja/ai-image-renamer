---
layout: home
hero:
  name: AI Image Renamer
  tagline: Automatically rename uploaded images using AI for SEO-friendly filenames.
  actions:
    - theme: brand
      text: Get Started
      link: /introduction/
    - theme: alt
      text: View on GitHub
      link: https://github.com/thaikolja/wp-ai-image-renamer
---

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vitepress';

const router = useRouter();

onMounted(() => {
  router.go('/introduction/');
});
</script>
