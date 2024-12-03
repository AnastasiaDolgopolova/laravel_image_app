<template>
    <div class="d-flex flex-column vh-100">
        <div class="container flex-grow-1 d-flex align-items-center">
            <div class="row w-100 h-100">
                <div class="col-md-6 d-flex">
                    <div class="border p-4 shadow rounded flex-grow-1 d-flex flex-column">
                        <h3 class="text-center mb-4">Add new post</h3>
                        <input v-model="title" type="text" class="form-control mt-3" placeholder="title">
                        <div ref="dropzone" class="btn d-block p-5 bg-dark text-center text-light mt-3">
                            Upload
                        </div>
                        <div class="mt-3">
                            <vue-editor useCustomImageHandler @image-added="handleImageAdded"
                                        v-model="content"></vue-editor>
                        </div>
                        <input @click.prevent="store" type="submit" class="btn btn-primary mt-3 w-100 mt-auto" value="Add">
                    </div>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="border p-4 shadow rounded flex-grow-1 d-flex flex-column">
                        <h3 class="text-center mb-4">Update last post</h3>
                        <input v-model="update_title" type="text" class="form-control mt-3" placeholder="title">
                        <div ref="update_dropzone" class="btn d-block p-5 bg-dark text-center text-light mt-3">
                            Upload
                        </div>
                        <div class="mt-3">
                            <vue-editor useCustomImageHandler @image-removed="handleImageRemoved" @image-added="handleImageAdded"
                                        v-model="update_content"></vue-editor>
                        </div>
                        <input @click.prevent="update" type="submit" class="btn btn-primary mt-3 w-100 mt-auto" value="Update">
                    </div>
                </div>
            </div>
        </div>

        <div class="container bg-light py-3">
            <div v-if="post">
                <h4>{{ post.title }}</h4>
                <div v-for="image in post.images" class="mb-3">
                    <img :src="image.preview_url" class="mb-3">
                    <img :src="image.url">
                </div>
                <div class="ql-editor" v-html="post.content"></div>
            </div>
        </div>
    </div>

</template>

<script>
import {Dropzone} from "dropzone";
import {VueEditor} from "vue3-editor";

export default {
    name: "Index",

    data() {
        return {
            dropzone: null,
            title: '',
            content: '',
            post: [],
            update_dropzone: null,
            update_title: '',
            update_content: '',
            imageIdsForDelete: [],
            imageUrlsForDelete: [],
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
        this.update_dropzone = new Dropzone(this.$refs.update_dropzone, {
            url: '/api/posts',
            autoProcessQueue: false,
            addRemoveLinks: true
        })

        this.update_dropzone.on('removedfile', (file) => {
            this.imageIdsForDelete.push(file.id);
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
            data.append('content', this.content);

            axios.post('/api/posts', data)
                // console.log(this.dropzone.getAcceptedFiles());
                .then(res => {
                    this.title = '';
                    this.content = '';
                    this.getLastPost();
                })
                .catch(error => console.log(error));
        },

        update() {
             const data = new FormData();
             const files = this.update_dropzone.getAcceptedFiles();
             files.forEach((file) => {
                 data.append(`images[]`, file);
                 this.update_dropzone.removeFile(file);
             });
             this.imageIdsForDelete.forEach( idForDelete => {
                 data.append('image_ids_for_delete[]', idForDelete);
             })

            this.imageUrlsForDelete.forEach( urlForDelete => {
                data.append('image_urls_for_delete[]', urlForDelete);
            })

             data.append('title', this.update_title);
             data.append('content', this.update_content);
             data.append('_method', 'PATCH');

             axios.post(`/api/posts/${this.post.id}`, data)
                 // console.log(this.dropzone.getAcceptedFiles());
                 .then(res => {
                     this.update_title = '';
                     this.update_content = '';
                     let previews = this.update_dropzone.previewsContainer.querySelectorAll('.dz-image-preview');

                     previews.forEach( preview => {
                         preview.remove();
                     })
                     this.getLastPost();
                 })
                 .catch(error => console.log(error));
        },

        getLastPost() {
            axios.get('/api/posts/last-post')
                .then(res => {
                    this.post = res.data.data;

                    this.update_title = this.post.title;
                    this.update_content = this.post.content;

                    this.post.images.forEach( image => {
                        let file = { name: image.name, size: image.size, id: image.id };
                        this.update_dropzone.displayExistingFile(file, image.preview_url);
                    })
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
        },

        handleImageRemoved(url) {
            this.imageUrlsForDelete.push(url);
        }
    }

}
</script>

<style>
.dz-success-mark,
.dz-error-mark {
    display: none;
}
</style>
