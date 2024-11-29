<template>
    <div class="w-25">
        <input v-model="title" type="text" class="form-control mt-3" placeholder="title">
        <div ref="dropzone" class="btn d-block p-5 bg-dark text-center text-light mt-3">
            Upload
        </div>
        <div class="mt-3">
            <vue-editor useCustomImageHandler @image-added="handleImageAdded" v-model="content"></vue-editor>
        </div>
        <input @click.prevent="store" type="submit" class="btn btn-primary mt-3" value="Add">
    </div>

    <div class="mt-5">
        <div v-if="post">
            <h4>{{ post.title }}</h4>
            <div v-for="image in post.images" class="mb-3">
                <img :src="image.preview_url" class="mb-3">
                <img :src="image.url">
            </div>
        </div>
    </div>
</template>

<script>
import {Dropzone} from "dropzone";
import { VueEditor } from "vue3-editor";
export default {
    name: "Index",

    data() {
        return {
            dropzone: null,
            title: '',
            post: [],
            content: ''
        }
    },

    components: {
        VueEditor,
    },

    mounted() {
        this.dropzone = new Dropzone(this.$refs.dropzone, {
            url: '/api/posts',
            autoProcessQueue: false,
            addRemoveLinks: true
        })
        this.getLastPost()
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
                    this.getLastPost();
                })
                .catch(error => console.log(error));
        },

        getLastPost() {
            axios.get('/api/posts/last-post')
                .then( res => {
                    this.post = res.data.data;
                })
        },

        handleImageAdded(file, Editor, cursorLocation, resetUploader) {
            const formData = new FormData();
            formData.append("image", file);

            axios.post('api/posts/images', formData)
                .then(result => {
                    const url = result.data.url; // Get url from response
                    Editor.insertEmbed(cursorLocation, "image", url);
                    resetUploader();
                })
                .catch(err => {
                    console.log(err);
                });
        }
    }

}
</script>

<style scoped>

</style>
