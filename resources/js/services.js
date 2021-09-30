export const url = {
    post(url) {
        return new Promise((resolve, reject) => {
            axios
                .post('/api/v1/urls', { url })
                .then(res => {
                    if (!!res.data.link) {
                        resolve({
                            link: res.data.link,
                            message: res.data.message ?? null
                        })
                    }
                    reject({
                        message: res.data.message ?? null
                    })
                })
                .catch(() => {
                    reject({
                        message: 'Server error.'
                    })
                })
        })
    }
}
