import { Tyrux } from "./lib/tyrux.js";

const baseURL = ""; //base url of your backend

const headers = {
    Authorization: "Bearer sometoken" 
};

const api = new Tyrux(headers);

function tyrux(request){
    api.request(request);
}

window.tyrux = tyrux;
window.baseURL = baseURL;