var q_time = 0;
var u_input = document.getElementById("uname");
//Create a Promise to fetch json url
function getHint(key, input, q_time) {
    // Return a new promise.
    return new Promise(function(resolve, reject) {

        // Do the usual XHR stuff
        var req = new XMLHttpRequest();
        req.open("POST", "/register");

        req.onload = function() {
            if (req.status == 200) {
                // Resolve the promise with the response obj
                resolve({response: req.responseText, q_time: q_time});
            } else {
                // Otherwise reject with the status text
                reject(new Error(req.statusText));
            }
        };

        // Handle network errors
        req.onerror = function() {
            reject(new Error("Network Error"));
        };

        req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        req.send(`${key}=${encodeURIComponent(input)}`);
        
    });
}

async function checkInput(el) {
    q_time = Date.now();
    var result = await getHint(el.id, el.value, q_time);
    if (result.q_time == q_time) {
        var data = JSON.parse(result.response);
        if (data[el.id]) {
            el.style.outline = "2px solid green";
        } else {
            el.style.outline = "2px solid red";
        }
    }
}

document.getElementById("uname").addEventListener("input", async function() {
    q_time = Date.now();
    var result = await getHint(this.id, this.value, q_time);
    if (result.q_time == q_time) {
        var data = JSON.parse(result.response);
        if (data[this.id]) {
            this.style.outline = "2px solid green";
        } else {
            this.style.outline = "2px solid red";
        }
    }
});