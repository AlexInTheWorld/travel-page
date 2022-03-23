function validate(raw_input) {
    var response = raw_input.trim().replace(/\d+|(?<=\s)\s+/g, "");
    var swaps = response.match(/\P{Alphabetic}/gu);
    
    if (swaps) {
        for (let i = 0; i < swaps.length; i++) {
            if ((/\s/).test(swaps[i])) {
                continue;
            }
            response = response.replace(swaps[i], "");
        }
    }

    return response;
}