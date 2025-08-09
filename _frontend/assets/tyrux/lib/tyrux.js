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

    constructor(config = {}) {
        if (config.headers) {
            let headers = config.headers;
            this.#defaultHeaders = { ...this.#defaultHeaders, ...headers };
        }
        if (config.baseURL && config.baseURL != null && config.baseURL !== "") {
            this.#baseURL = config.baseURL;
        }
        this.#config = config;
    }

    request(options) {
        const xhr = new XMLHttpRequest();
        const method = options.method ? options.method.toUpperCase() : "GET";
        let url = this.#baseURL + options.url;
        let data = null;

        const headers = options.headers ? { ...this.#defaultHeaders, ...options.headers } : this.#defaultHeaders;
        const contentType = headers["Content-Type"] || "";

        if (options.data && typeof options.data === 'object') {
            if (method === "GET") {
                const params = Object.keys(options.data)
                    .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(options.data[key])}`)
                    .join('&');
                url += (url.includes('?') ? '&' : '?') + params;
            } else {
                if (contentType.includes("application/json")) {
                    data = JSON.stringify(options.data);
                } else if (contentType.includes("application/x-www-form-urlencoded")) {
                    data = Object.keys(options.data)
                        .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(options.data[key])}`)
                        .join('&');
                } else {
                    data = options.data;
                }
            }
        }

        xhr.open(method, url, true);

        for (const h in headers) {
            xhr.setRequestHeader(h, headers[h]);
        }

        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                let responseData = xhr.responseText;
                const respContentType = xhr.getResponseHeader("Content-Type") || "";

                if (respContentType.includes("application/json")) {
                    try {
                        responseData = JSON.parse(xhr.responseText);
                    } catch (e) {
                        console.warn("Failed to parse JSON response:", e);
                    }
                }

                if (xhr.status >= 200 && xhr.status < 300) {
                    options.success?.(responseData, xhr);
                    options.response?.(responseData, xhr);
                } else {
                    if (this.#config?.error) {
                        if (this.#config.error === "console") {
                            console.error(responseData.message ?? xhr.statusText);
                        }
                        if (this.#config.error === "alert") {
                            alert(responseData.message ?? xhr.statusText);
                        }
                        if (this.#config.error === "log") {
                            console.log(responseData.message ?? xhr.statusText);
                        }
                    }
                    options.error?.(responseData, xhr);
                }
            }
        };

        xhr.send(data);
    }
}
