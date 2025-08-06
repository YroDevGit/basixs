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

    constructor(headers = {}, baseURL="") {
        this.#defaultHeaders = { ...this.#defaultHeaders, ...headers };
        this.#baseURL = baseURL;
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
                    options.error?.(xhr.statusText, xhr);
                }
            }
        };

        xhr.send(data);
    }
}
