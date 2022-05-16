var city_input  = document.getElementById("city");
var suggestions = document.getElementById("suggestions");
var info_el     = document.getElementById("info-msg");
var results_el  = document.getElementById("results-view");
var bg_input    = document.getElementById("background-input");
var comment_posting = false; // Default value
var stack       = {frontier_time: 0 /* ignore queries before it */, term: "", timestamp: 0}; // Pivotal data for AJAX calls for async processing
 
const extra_interval = 200; // in milliseconds, min time imposed between queries
const base_url_city = "/search?city=";
const base_url_dbcity = "/city?geonameId=";
const base_photo_url = "https://api.teleport.org/api/cities/geonameid:";
const max_num_hints = 5;
const properties = ["geonameId", "name", "lat", "lng" , "adminName1", "countryName"]; // City details 

function removeChildElements(el) {
  while (el.lastElementChild) {
    el.lastElementChild.remove();
  }
}

function showHints(hints /* array with collection of cities' objects */) {
  var num_hints = hints.length;
  var num_li_to_modify = Math.min(num_hints, suggestions.children.length);
  var num_li_to_remove_or_add = num_hints - suggestions.children.length;
  var current_li = suggestions.firstElementChild;
  var txt = "";
  var idx = 0;

  if (num_hints > 0) {

    for (let i = 0; i < num_li_to_modify; i++) { /* Modify text and id to existing <li>s */
      current_li.textContent = `${hints[i].name} (${hints[i].adminName1 ? hints[i].adminName1 + ", " : ""}${hints[i].countryName})`;
      current_li.setAttribute("onclick", `submitQuery(${JSON.stringify(hints[i])})`);
      current_li = current_li.nextElementSibling;
      idx++;
    }
  
    if (num_li_to_remove_or_add > 0) { /* Create additional <li>s */
      for (let i = 0; i < num_li_to_remove_or_add; i++) {
        current_li = document.createElement("LI");
        txt = document.createTextNode(`${hints[idx].name} (${hints[idx].adminName1 ? hints[i].adminName1 + ", " : ""}${hints[idx].countryName})`);
        current_li.appendChild(txt);
        current_li.setAttribute("onclick", `submitQuery(${JSON.stringify(hints[idx])})`);
        current_li.classList.add("city-option");
        suggestions.appendChild(current_li);
        idx++;
      }
    } else if (num_li_to_remove_or_add < 0) { /* Remove redundant <li>s */
      for (let i = 0; i < Math.abs(num_li_to_remove_or_add); i++) {
        suggestions.lastElementChild.remove();
      }
    }

  } else {
    removeChildElements(suggestions);
  }

}
// Process successfully resolved ajax calls for global city searches
function handleResolved(result) {
  var filtered_data = result.data ? siftResults(JSON.parse(result.data), result.search_term) : [];
  
  if (result.q_time > stack.frontier_time && result.search_term === stack.term) {
    showHints(filtered_data);
  }
  stack.frontier_time = result.q_time; // Deny the possibility of processing queries initiated before the current one
  
  return filtered_data;
}
// Process rejected ajax calls for global city searches
function handleRejected(msg) {
  console.error(msg);
  removeChildElements(suggestions);
}

//Create a Promise to fetch json url
function getHint(method, base_url, search_term, q_time, appended_url="") {
  // Return a new promise.
  return new Promise(function(resolve, reject) {
      if (search_term) {
          // Do the usual XHR stuff
          var req = new XMLHttpRequest();
          var url = method.toLocaleLowerCase() === "get" ? base_url + encodeURIComponent(search_term) + appended_url : base_url;
          
          req.open(method, url);

          req.onload = function() {
            if (req.status == 200) {
              // Resolve the promise with the response obj
              resolve({data: req.responseText, search_term: search_term, q_time: q_time});
            } else {
              // Otherwise reject with the status text
              reject(new Error(req.statusText));
            }
          };

          // Handle network errors
          req.onerror = function() {
            reject(new Error("Network Error"));
          };

          if (method.toLocaleLowerCase() === "post") {
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.send(`geonameId=${encodeURIComponent(search_term)}`);
          } else {
            req.send();
          } 
      } else {
        resolve({search_term: search_term, q_time: q_time});
      }

  });
}

function decideResponse() {
  return new Promise(resolve => {
      setTimeout(() => {
        var query_time = stack.timestamp;
        var current_time = Date.now();
        resolve(current_time - query_time >= extra_interval || !stack.term ? {search_term: stack.term, q_time: query_time} : null)
      }, extra_interval)
  });
}

function siftResults(raw_data, term) {
  var cities   = [];
  var incoming = {};

  for (let i = 0; i < raw_data.geonames.length; i++) {
      
      if (raw_data.geonames[i]["name"].toLowerCase().search(term) === 0 && !/^[A-Z]{3,}$/g.test(raw_data.geonames[i]["name"])) {

        properties.forEach((key) => incoming[key] = raw_data.geonames[i][key] );

        for (let j = 0; j < max_num_hints; j++) {
          if (!cities[j]) {
            cities[j] = {};
            for (let key in incoming) { cities[j][key] = incoming[key] }
            break;
          } else if (cities[j].name > incoming.name) {
              for (let key in cities[j]) { 
                [cities[j][key], incoming[key]] = [incoming[key], cities[j][key]]
              }
          }  
        }
          
      }
      
  }

  return cities;
}

function allowSubmitComment(submit_button) {
  submit_button.setAttribute("type", "submit");
}

function showPostCommentForm(button) {
  button.textContent = "Submit";
  button.setAttribute("onclick", "allowSubmitComment(this);");
  var text_area = document.createElement("TEXTAREA");
  var required_attr = document.createAttribute("required");
  text_area.setAttributeNode(required_attr);
  text_area.setAttribute("name", "comment");
  text_area.setAttribute("rows", "4");
  text_area.setAttribute("cols", "50");
  
  var input = document.createElement("INPUT");
  input.setAttribute("type", "hidden");
  input.setAttribute("name", "geonameId");
  input.setAttribute("value", sessionStorage.getItem("geonameId"));
  button.parentNode.insertBefore(text_area, button);
  button.parentNode.insertBefore(input, text_area);
}

function displayComments(json_str) {
  var data = JSON.parse(json_str);
  var data_len = data.length;
  
  var span_intro = document.createElement("SPAN");
  var span_intro_txt = document.createTextNode("Results for: ");
  span_intro.appendChild(span_intro_txt);
  span_intro.setAttribute("class", "span-intro");
  
  var span_info = document.createElement("SPAN");
  var span_info_txt = document.createTextNode(`${sessionStorage.getItem("name")} (${sessionStorage.getItem("countryName")}) [lat:${sessionStorage.getItem("lat")}, long: ${sessionStorage.getItem("lng")}]`);
  span_info.appendChild(span_info_txt);
  span_info.setAttribute("class", "span-info");
  info_el.appendChild(span_intro);
  info_el.appendChild(span_info);

  if (data_len === 0) {
    var no_comments_el = document.createElement("P");
    var no_comments_txt = document.createTextNode("No comments yet.");
    no_comments_el.appendChild(no_comments_txt);
    no_comments_el.setAttribute("class", "no-comments-msg");
    results_el.appendChild(no_comments_el);
  } else {
      for (let i = 0; i < data_len; i++) {
        var comment_el = document.createElement("DIV");
        comment_el.setAttribute("class", "comment");
        var header_el = document.createElement("P");
        var header_txt = document.createTextNode(data[i].user + " on " + data[i].date + " wrote:");
        var main_content_el = document.createElement("P");
        var main_content_txt = document.createTextNode(data[i].comment);
        header_el.appendChild(header_txt);
        main_content_el.appendChild(main_content_txt);
        comment_el.appendChild(header_el);
        comment_el.appendChild(main_content_el);
        results_el.appendChild(comment_el);
      }
  }
  // If user has logged in, allow possibility to post a comment
  if (document.getElementById("logged_el")) {
    var form = document.createElement("FORM");
    form.setAttribute("method", "POST");
    form.setAttribute("action", "#");
    var btn = document.createElement("BUTTON");
    btn.setAttribute("type", "button");
    btn.setAttribute("onclick", "showPostCommentForm(this)");
    btn.textContent = "Post a comment";
    form.appendChild(btn);
    results_el.appendChild(form);
  }
}

function changeBackground(img_details) {
  var data = img_details ? JSON.parse(img_details.data) : null;
  var photo_link = data ? screen.width > 600 ? data.photos[0].image.web : data.photos[0].image.mobile : '/public/city.jpg';
  
  document.body.style.backgroundImage = `url(${photo_link})`;
}

function getPhotoDetails(links_obj) {
  if (links_obj._links["city:urban_area"]) {
      return getHint("GET", links_obj._links["city:urban_area"].href.replace(/\/$/, ""), "/images/", Date.now());
  }
  return;
}

city_input.addEventListener("input", async function() {
  stack.term = validate(this.value);
  stack.timestamp = Date.now();
  // Process ajax response when input is empty or remains unchanged for the time defined by extra_interval
  var last_call = await decideResponse(); 
  if (last_call) {
      return getHint("GET", base_url_city, last_call.search_term, last_call.q_time).then(handleResolved, handleRejected);
  }
});

function inProgressOn() {
  disableInputControls();
  removeChildElements(suggestions);
  removeChildElements(results_el);
  removeChildElements(info_el);
  
  var txt_el = document.createElement("P");
  var txt = document.createTextNode("Loading...");
  txt_el.appendChild(txt);
  txt_el.setAttribute("class", "flickering");
  info_el.appendChild(txt_el);
}

function inProgressOff() {
  removeChildElements(info_el);
  info_el.classList.remove("flickering");
  enableInputControls();
}

function queryErrMsg(msg) {
  var txt_el = document.createElement("SPAN");
  var txt = document.createTextNode(msg);
  txt_el.appendChild(txt);
  info_el.appendChild(txt_el);
  
  var close_el = document.createElement("SPAN");
  var close_symbol = document.createTextNode("⊠");
  close_el.setAttribute("class", "x-times")
  close_el.appendChild(close_symbol);
  close_el.setAttribute("onclick", "removeChildElements(info_el)");
  info_el.appendChild(close_el);
}

async function handleSubmitObj(data) {
  var inbound = {};
  
  try {
    inbound.comments = await getHint("GET", base_url_dbcity, data.geonameId, Date.now());
    let city_links = await getHint("GET", base_photo_url, data.geonameId, Date.now());
    inbound.photo_links = await getPhotoDetails(JSON.parse(city_links.data));
  } 
  catch(e) {
    console.error(e);
  }
  finally {
    inProgressOff();   
    changeBackground(inbound.photo_links);
    
    if (inbound.comments) {
      for (let key in data) { sessionStorage.setItem(key, data[key]) }
      displayComments(inbound.comments.data);
    } else {
      if (sessionStorage.getItem("geonameId")) {
        queryErrMsg("Ooops, an error occured in the process. Try again.");
      }
    }
    
  }
    
}

async function handleSubmitStr(term) {
    
  var outbound;
  
  try {
    outbound = await getHint("GET", base_url_city, term, stack.frontier_time);
    outbound = handleResolved(outbound);
  }
  catch(e) {
    console.error(e);
  }
  finally {
    if (outbound) {        
      switch(outbound.length) {
        case 0:
          inProgressOff();
          queryErrMsg("No such city found");
          break;
        case 1:
          handleSubmitObj(outbound[0]);
          break;
        default:
          inProgressOff();
          alert("Select a city");
          showHints(outbound);
      }

    } else {
      inProgressOff();
      queryErrMsg("Ooops, an error occured in the process. Try again.");
    }
  }

}

function disableInputControls() {
  city_input.setAttributeNode(document.createAttribute("disabled"));
  document.querySelector("button").setAttributeNode(document.createAttribute("disabled"));
}

function enableInputControls() {
  city_input.removeAttribute("disabled");
  document.querySelector("button").removeAttribute("disabled");
}

function submitQuery(dataload=null) {
  stack.frontier_time = Date.now();
  var term = validate(city_input.value);
  inProgressOn();
  
  if (dataload) {
    handleSubmitObj(dataload);            
  } else {
    if (term) {
      handleSubmitStr(term);
    }
  }
}

document.querySelector("form").addEventListener("submit", function(e) {
  e.preventDefault();
  submitQuery();
});

function init() {
  var payload = {};
  if (sessionStorage.getItem(properties[0])) {
    inProgressOn();
    properties.forEach((key) => payload[key] = sessionStorage.getItem(key));
    handleSubmitObj(payload);
  }    
}

window.onload = init();