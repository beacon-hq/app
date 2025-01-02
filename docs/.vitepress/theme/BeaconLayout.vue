<!--.vitepress/theme/MyLayout.vue-->
<script setup>
import Frontpage from './Frontpage.vue';
import { Content, useData } from 'vitepress';

const { page, frontmatter } = useData();
</script>

<template>
    <div>
        <div v-if="page.isNotFound">Custom 404 page!</div>
        <Frontpage v-if="frontmatter.layout === 'home'" />
        <Content v-else />
    </div>
</template>
