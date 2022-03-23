var city_input  = document.getElementById("city");
var cities      = document.getElementById("cities");
var suggestions = document.getElementById("suggestions");
var incoming    = {geonameId: "", name: "", lat: null, lng: null, countryName: ""};

const base_url  = "localhost";
const username  = "aliaksandrk";
const max_num_hints = 5;

function removeSuggestions() {
    while (suggestions.lastElementChild) {
        suggestions.lastElementChild.remove();
    }
}

function clickOnMe(li) {
  alert("You clicked me!");
}

function showHints(cities_obj) {
  var num_hints = cities_obj.name.length;
  var num_li_to_modify = Math.min(num_hints, suggestions.children.length);
  var num_li_to_remove_or_add = num_hints - suggestions.children.length;
  var current_li = suggestions.firstElementChild;
  var txt = "";
  var idx = 0;

  if (num_hints > 0) {

    for (let i = 0; i < num_li_to_modify; i++) {
      // current_li.setAttribute("onclick", "clickOnMe(this)");
      current_li.textContent = cities_obj.name[i] + " (" + cities_obj.countryName[i] + ")";
      current_li = current_li.nextElementSibling;
      idx++;
    }
  
    if (num_li_to_remove_or_add > 0) {
      for (let i = 0; i < num_li_to_remove_or_add; i++) {
        current_li = document.createElement("LI");
        txt = document.createTextNode(cities_obj.name[idx] + " (" + cities_obj.countryName[idx] + ")");
        current_li.appendChild(txt);
        current_li.setAttribute("onclick", "clickOnMe(this);")
        suggestions.appendChild(current_li);
        idx++;
      }
    } else if (num_li_to_remove_or_add < 0) {
      for (let i = 0; i < Math.abs(num_li_to_remove_or_add); i++) {
        suggestions.lastElementChild.remove();
      }
    }

  } else {
    removeSuggestions();
  }
 console.log("-------------------------")
}

//Create a Promise to fetch json url
function getHint(url) {
    // Return a new promise.
    return new Promise(function(resolve, reject) {
      // Do the usual XHR stuff
      var req = new XMLHttpRequest();
      req.open('GET', url);
  
      req.onload = function() {
        if (req.status == 200) {
          // Resolve the promise with the response text
          resolve(req.responseText);
        }
        else {
          // Otherwise reject with the status text
          reject(Error(req.statusText));
        }
      };
  
      // Handle network errors
      req.onerror = function() {
        reject(Error("Network Error"));
      };
  
      // Make the request
      req.send();
    });
  }

city_input.addEventListener("input", function() {
    var u_input = validate(this.value);
    console.log(u_input);
  
    if (u_input) {
        getHint(`/search?city=${encodeURIComponent(u_input)}`)
        .then(JSON.parse)
        .then((data) => {
          console.log(data);
          /*
          var cities = {geonameId: [], name: [], lat: [], lng: [], countryName: []};

          for (let i = 0; i < data.geonames.length; i++) {
            if (data.geonames[i]["name"] === data.geonames[i]["toponymName"] && 
            data.geonames[i]["name"].toLowerCase().search(u_input) === 0) {

              Object.keys(incoming).forEach((key) => incoming[key] = data.geonames[i][key] );

              for (let j = 0; j < max_num_hints; j++) {
                if (!cities.name[j]) {
                  Object.keys(cities).forEach((key) => cities[key][j] = incoming[key]);
                  break;
                } else if (cities.name[j] > incoming.name) {
                  Object.keys(cities).forEach((key) => {
                    [cities[key][j], incoming[key]] = [incoming[key], cities[key][j]]
                  });
                }
              }

            }
          }

           showHints(cities); */
        });
        
    } else {
        removeSuggestions();
    }
        
})

