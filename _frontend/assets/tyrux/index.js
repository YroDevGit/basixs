import { Tyrux } from "./lib/tyrux.js";

const baseURL = "";  //Backend url end-point
const baseRoute = "?be=";  // Your app default backend routing

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


window.tyrux = tyrux;
window.baseURL = baseURL;
window.baseRoute = baseRoute;
