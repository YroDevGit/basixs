import { Tyrux } from "./lib/tyrux.js";
import { DOMclass } from "./lib/functions.js";

const baseURL = "";   //Backend url end-point
const baseRoute = "";   // Default api rout
const backend = "?be=";  // This app default backend path

const headers = {
    Authorization: "Bearer sometoken", 
};

const config = {
    error: "alert",
    headers: headers,
    baseURL:  backend
};

const api = new Tyrux(config);

function tyrux(request){// Use for default setup..:: CodeYRO
    api.request(request);
}

//Exports here...
window.tyrux = tyrux;
window.baseURL = baseURL;
window.baseRoute = baseRoute;
window.backend = backend;


/**
 * tyruxRequest is a raw request
 * above setup doesn't apply here, but you can use them and attach to tyruxRequest
 */

const tyruxRequest = {// For raw/universal request:: CodeYRO
    api: function(option){
        api.request(option);
    },
    post: function (option){
        option.method = "POST";
        api.request(option);
    },
    put: function (option){
        option.method = "PUT";
        api.request(option);
    },
    get: function (option){
        option.method = "GET";
        api.request(option);
    },
    patch: function (option){
        option.method = "PATCH";
        api.request(option);
    },
    delete: function (option){
        option.method = "DELETE";
        api.request(option);
    },
    head: function (option){
        option.method = "HEAD";
        api.request(option);
    },
    options: function (option){
        option.method = "OPTIONS";
        api.request(option);
    },
}

function get_form_data(selector) {
    let form = null;
    if (selector.charAt(0) === "#" || selector.charAt(0) === ".") {
        form = document.querySelector(selector);
    } else {
        form = document.querySelector(`#${selector}`);
    }
    if (!form) return null;
    const formData = new FormData(form);
    const dataObject = {};
    formData.forEach((value, key) => {
        dataObject[key] = value;
    });
    return dataObject;
}

const DOM = new DOMclass();

window.get_form_data = get_form_data;
window.tyruxRequest = tyruxRequest;
window.DOM = DOM;
