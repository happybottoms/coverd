<template>
    <div class="form-group">
        <label
            v-if="label"
            v-text="label"
        />
        <div v-text="value" />
    </div>
</template>

<script>
    export default {
        name: 'DisplayField',
        props: {
            label: { type: [String, Boolean], default: false },
            value: { type: [String, Number] },
        }
    }
</script>