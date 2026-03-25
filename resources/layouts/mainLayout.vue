<template>
    <div class="layout" style="width:100%; max-width:100%; min-width:0; overflow-x:hidden;">
        <Header />
        <main class="layout__content" style="width:100%; max-width:100%; min-width:0; overflow:hidden;">
            <slot/>
        </main>
        <Footer />
    </div>
</template>

<script setup>
import Header from '../components/header.vue'
import Footer from '../components/footer.vue'
</script>
