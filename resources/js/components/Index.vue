<template>
    <div class="w-25">
        <input v-model="title" type="text" class="form-control mt-3" placeholder="title">
        <div ref="dropzone" class="btn d-block p-5 bg-dark text-center text-light mt-3">
            Upload
        </div>
        <input @click.prevent="store" type="submit" class="btn btn-primary mt-3" value="Add">
    </div>

    <div class="mt-5">
        <div v-if="post">
            <h4>{{ post.title }}</h4>
            <div v-for="image in post.images" class="mb-3">
                <img :src="image.url">
            </div>
        </div>
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
            post: []
        }
    },

    mounted() {
        this.dropzone = new Dropzone(this.$refs.dropzone, {
            url: '/api/posts',
            autoProcessQueue: false,
            addRemoveLinks: true
        })
        this.getPosts()
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
        },

        getPosts() {
            axios.get('/api/posts')
                .then( res => {
                    this.post = res.data.data;
                })
        }
    }

}
</script>

<style scoped>

</style>
