<template>
    <div class="form-group">
        <label
            v-if="label"
            v-text="label"
        />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <input
            type="file"
            :placeholder="placeholder"
            @change="onFilePicked"
        />
    </div>
</template>

<script>
export default {
    name: "FileUploadField",
    props: {
        label: { type: [String, Boolean], default: false },
        value: { type: Object, default: () => {} },
        placeholder: { type: String, required: false, default: "" },
        helpText: { type: String, required: false, default: "" },
    },
    methods: {
        onFilePicked (event) {
            const files = event.target.files;
            const fileReader = new FileReader();
            fileReader.addEventListener('load', () => {
                let data = {};
                data.filename = files[0].name;
                data.filesize = files[0].size;
                data.mimeType = files[0].type;
                data.content = fileReader.result;
                this.$emit('input', data);
            });
            fileReader.readAsDataURL(files[0]);
        }
    }
}
</script>

