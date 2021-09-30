<template>
    <div class="my-5 form-wrapper">
        <b-card bg-variant="dark" text-variant="white" title="Let's short your URL">
            <b-card-text>
                Please provide a valid http(s) link.
            </b-card-text>
            <b-form @submit.pre.prevent="onSubmit" @reset="onReset">
                <div class="--group">
                    <b-form-input
                        v-model="url"
                        @change="validateUrl"
                        :readonly="isProcessing !== null"
                        type="url" required
                        autofocus autocomplete="off"
                        placeholder="https://..."></b-form-input>
                    <b-button type="reset" variant="secondary" v-if="isProcessing !== null">Clear</b-button>
                </div>
                <div class="mt-3 mb-1">
                    <p v-if="hint.length > 0" class="hint">{{ hint }}</p>
                    <b-button v-if="isProcessing === null" type="submit" variant="primary">Get a short link</b-button>
                    <b-card-text v-else-if="isProcessing === true">
                        <b-spinner small class="me-2" /> Processing ...
                    </b-card-text>
                    <template v-else-if="newLink">
                        <b-card-text class="d-flex flex-row align-items-center">
                            <span class="d-inline-block me-3 --no-wrap">Your Link:</span>
                            <a class="btn btn-link bg-secondary text-light --wb-all" target="_blank" :href="newLink">{{ newLink }}</a>
                        </b-card-text>
                        <p class="hint mt-2">Warning: This link will expire in 30 days.</p>
                    </template>
                </div>
            </b-form>
        </b-card>
    </div>
</template>

<script>
import {url as api} from "../services";

export default {
    name: "UrlRegistrationForm",
    data() {
        return {
            url: '',
            hint: '',
            newLink: '',
            hasError: false,
            isProcessing: null,
        }
    },
    methods: {
        onSubmit() {
            this.validateUrl()
            if (!this.hasError && this.url.length > 0) {
                this.isProcessing = true
                api.post(this.url)
                    .then(res => {
                        this.newLink = res.link
                    })
                    .catch(err => {
                        if (!!err.message) {
                            if (typeof err.message === 'object') {
                                if (!!err.message.url[0]) {
                                    this.hint = err.message.url[0]
                                    return
                                }
                                this.hint = 'Unknown error.'
                            }
                            this.hint = err.message
                        }
                    })
                    .finally(() => {
                        this.isProcessing = false
                    })
            }
        },
        onReset() {
            this.url = ''
            this.hint = ''
            this.newLink = ''
            this.hasError = false
            this.isProcessing = null
        },
        validateUrl() {
            if (this.url.length > 0 && this.url.length < 11) {
                this.hasError = true
                this.hint = 'The URL must be at least 11 characters long.'
            } else {
                this.hasError = false
                this.hint = ''
            }
        },
    },
}
</script>

<style scoped lang="scss">
.form-wrapper {
    max-width: 720px;
}
p.hint {
    font-size: 0.8em;
    margin-bottom: 0;
    opacity: .75;
}
.--group {
    display: flex;
    flex-direction: row;
    > input {
        flex: auto;
    }
    > button {
        margin-left: 12px;
        flex: 70px 0 0;
    }
}
.--no-wrap {
    white-space: nowrap;
}
.--wb-all {
    word-break: break-all;
}
</style>
