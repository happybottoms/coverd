<template>
    <div class="container">
        <div
            v-if="loading"
            class="ph-item skeletonContainer"
        >
            <div class="ph-col-12">
                <div class="ph-row">
                    <div class="ph-col-6" />
                    <div class="ph-col-6 empty" />
                    <div class="ph-col-12" />
                    <div class="ph-col-4" />
                    <div class="ph-col-8 empty" />
                    <div class="ph-col-6" />
                    <div class="ph-col-6 empty" />
                    <div class="ph-col-12" />
                </div>
            </div>
        </div>
        <div :class="loading ? 'hide' : 'show'">
            <slot />
        </div>
    </div>
</template>

<script>
export default {
    name: "TableSkeleton",
    props: {
        loading: { type: Boolean, default: true }
    }
};
</script>

<style scoped>
.container {
    position: relative;
}
.skeletonContainer {
    border: none;
}
.hide {
    opacity: 0;
    width: 10px;
    height: 10px;
    overflow: hidden;
}
.show {
    opacity: 1;
    width: inherit;
    height: inherit;
    overflow: visible;
}
</style>
