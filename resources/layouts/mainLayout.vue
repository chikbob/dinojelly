<template>
    <div class="layout">
        <Header />
        <main class="layout__content">
            <slot/>
        </main>
        <Footer />
    </div>
</template>

<script setup>
import Header from '../components/header.vue'
import Footer from '../components/footer.vue'
</script>
