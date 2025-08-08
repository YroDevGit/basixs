import { Tyrux } from "./lib/tyrux.js";

const baseURL = "";   //Backend url end-point
const baseRoute = "";   // Default api rout
const backend = "?be=";  // This app default backend path

const headers = {
    Authorization: "Bearer sometoken" 
};

const config = {
    error: "console",
    headers: headers,
};



const api = new Tyrux(config);

function tyrux(request){
    api.request(request);
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



window.tyrux = tyrux;
window.get_form_data = get_form_data;
window.baseURL = baseURL;
window.baseRoute = baseRoute;
window.backend = backend;
