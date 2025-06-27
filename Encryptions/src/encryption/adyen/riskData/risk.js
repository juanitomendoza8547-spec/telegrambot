const RiskData = require("./riskData.js")
const randomUseragent = require('random-useragent');

const ua = randomUseragent.getRandom();

function RIskGen(){
    let a = new RiskData(
        ua,
        "en-US",
        24,
        4,
        8,
        360,
        640,
        360,
        640,
        -300,
        "America/Chicago",
        "MacIntel",
    )
    rData = a.generate()
    if(rData){
        return rData;
    }else{
        throw new Error("Error while generating riskData.")
    }

} 


module.exports = RIskGen;