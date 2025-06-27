const fs = require("fs");
const path = require("path");

const encryptCardData441 = require("../../encryption/adyen/adyen-4.4.1/encrypt");
const encryptCardData511 = require("../../encryption/adyen/adyen-5.11.0/index");
const encryptCardData450 = require("../../encryption/adyen/adyen-4.5.0/encrypt");

async function Adyen(req) {
  const { adyenkey, card, month, year, cvv, version, ppkey, domain } = req;

  if (!adyenkey) {
    return { error: "Missing required `PUBLIC ADYEN KEY`." };
  }

  let encryptedData;

  try {
    switch (version) {
      case "25":
        encryptedData = encryptCardData511(card, month, year, cvv, adyenkey);
        break;
      case "v4":
        encryptedData = encryptCardData441(card, month, year, cvv, adyenkey);
        break;
      case "v2":
        encryptedData = encryptCardData450(
          card,
          month,
          year,
          cvv,
          adyenkey,
          ppkey,
          domain
        );
        break;
      default:
        return { error: "Invalid version specified in payload" };
    }

    return { encryptedData };
  } catch (error) {
    return { status: false, error_message: error.message };
  }
}

if (require.main === module) {
  const args = process.argv.slice(2);
  const req = {
    adyenkey: args[0],
    card: args[1],
    month: args[2],
    year: args[3],
    cvv: args[4],
    version: args[5],
    ppkey: args[6],
    domain: args[7]
  };

  Adyen(req).then((result) => {
    console.log(JSON.stringify(result));
  }).catch((error) => {
    console.error(JSON.stringify({ error: error.message }));
  });
}

module.exports = { Adyen };
