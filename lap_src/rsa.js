// some utility functions for RSA crypto

//see https://developer.mozilla.org/en-US/docs/Web/API/SubtleCrypto/importKey#pkcs_8_import
function str2ab(str) {
  const buf = new ArrayBuffer(str.length);
  const bufView = new Uint8Array(buf);
  for (let i = 0, strLen = str.length; i < strLen; i++) {
    bufView[i] = str.charCodeAt(i);
  }
  return buf;
}

/*
Import a PEM encoded RSA private key, to use for RSA-PSS signing.
Takes a string containing the PEM encoded key, and returns a Promise
that will resolve to a CryptoKey representing the private key.
*/

function importPrivateKey(pem) {
  // fetch the part of the PEM string between header and footer
  const pemHeader = "-----BEGIN PRIVATE KEY-----";
  const pemFooter = "-----END PRIVATE KEY-----";
  const pemContents = pem.substring(
    pemHeader.length,
    pem.length - pemFooter.length
  );
  // base64 decode the string to get the binary data
  const binaryDerString = window.atob(pemContents);
  // convert from a binary string to an ArrayBuffer
  const binaryDer = str2ab(binaryDerString);

  return window.crypto.subtle.importKey(
    "pkcs8",
    binaryDer,
	   {
	     name: "RSASSA-PKCS1-v1_5",
	     hash: "SHA-256"
	   },
	false, //true,
    ["sign"]
  );
}

/**
 * Get a promise which will generate the signature for a message 
 */
function sign(privateKey, message){
	const te=new TextEncoder("UTF-8"); 
	return window.crypto.subtle.sign(
		"RSASSA-PKCS1-v1_5",
	  	privateKey,
	  	te.encode(message)).then((signature) => {
	  		return btoa(String.fromCharCode(...new Uint8Array(signature)));
	  	}) ;
  	
}

