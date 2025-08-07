export class Tyrux {
    /**
     * @Author : Tyrone Limen Malocon
     * @Created : Aug 6 2025
     * @Country : Philippines
     * @Email : tyronemalocon@gmail.com
     */
    #defaultHeaders = {
        "Content-Type": "application/x-www-form-urlencoded"
    };
    #baseURL = "";

    #config = {};

    constructor(config={}) {
        if(config.headers){
            let headers = config.headers;
            this.#defaultHeaders = { ...this.#defaultHeaders, ...headers };
        }
        if(config.baseURL && config.baseURL != null && config.baseURL != ""){
            this.#baseURL = config.baseURL;
        }
        this.#config = config;
    }

    request(options) {
        const xhr = new XMLHttpRequest();
        const method = options.method ? options.method.toUpperCase() : "GET";
        let url = this.#baseURL+options.url;
        let data = null;

        if (options.data && typeof options.data === 'object') {
            const params = Object.keys(options.data)
                .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(options.data[key])}`)
                .join('&');
            data = params;
        }

        if (method === "GET" && data) {
            url += (url.includes('?') ? '&' : '?') + data;
            data = null;
        }

        xhr.open(method, url, true);

        const headers = options.headers ? { ...this.#defaultHeaders, ...options.headers } : this.#defaultHeaders;

        for (const h in headers) {
            xhr.setRequestHeader(h, headers[h]);
        }

        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    options.success?.(xhr.responseText, xhr);
                } else {
                    const errB = xhr.statusText;
                    const xhrB = xhr;
                    if(this.#config?.error){
                        if(this.#config.error == "console"){
                            console.error(errB);
                        }
                        if(this.#config.error == "alert"){
                            alert(errB);
                        }
                        if(this.#config.error == "log"){
                            console.log(errB);
                        }
                    }
                    options.error?.(errB, xhrB);
                }
            }
        };

        xhr.send(data);
    }
}
