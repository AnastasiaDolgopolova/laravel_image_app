<template>
    <div class="w-25">
        <input v-model="title" type="text" class="form-control mt-3" placeholder="title">
        <div ref="dropzone" class="btn d-block p-5 bg-dark text-center text-light mt-3">
            Upload
        </div>
        <input @click.prevent="store" type="submit" class="btn btn-primary mt-3" value="Add">
    </div>
</template>

<script>
import {Dropzone} from "dropzone";
export default {
    name: "Index",

    data() {
        return {
            dropzone: null,
            title: '',
        }
    },

    mounted() {
        this.dropzone = new Dropzone(this.$refs.dropzone, {
            url: '/api/posts',
            autoProcessQueue: false,
            addRemoveLinks: true
        })
    },

    methods: {
        store() {
            const data = new FormData();
            const files = this.dropzone.getAcceptedFiles();
            files.forEach((file) => {
                data.append(`images[]`, file);
                this.dropzone.removeFile(file);
            });
            data.append('title', this.title);

            axios.post('/api/posts', data)
           // console.log(this.dropzone.getAcceptedFiles());
                .then(res => {
                    this.title = '';
                    console.log(res);
                })
                .catch(error => console.log(error));
        }
    }

}
</script>

<style scoped>

</style>
