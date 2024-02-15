/**
 * Utility functions for client-to-server comunications.
 */

/**
 * Create a promise to send a post request to the outbox endpoind 
 * with the specified activity as body. Notice that here we define a custom
 * header X-OpenDataHacklab-activitydate because the date header must be under the
 * full control of the user agent, see https://fetch.spec.whatwg.org/#forbidden-header-name.

 * @param activity body of the POST request
 * @param date date for the date header field
 * @param signature signature header
 * @param outbox target endpoint for the POST request
 * @return a promise to perform the post request
 */
function postActivityPromise(activity, date, digest, signature, outbox){
	return fetch(outbox, {
		method: "POST",
		cors: "no-cors",
		body: activity,
		headers: {
			"Content-type": "application/ld+json; profile=\"https://www.w3.org/ns/activitystreams\"",
			"X-Opendatahacklab-Activitydate": date,
			"Digest": digest,
			"Signature": signature
		}
	});
}

/**
 * Create a digest of the activity field and use it to set the value of the digest field
 * @param activity the activity 
 * @return a promise to create the activity digest
 */
function digestPromise(activity){
  const te=new TextEncoder("UTF-8"); 
  const buff=te.encode(activity);
  return window.crypto.subtle.digest('SHA-256', buff).then(param => {
  	// https://stackoverflow.com/questions/9267899/arraybuffer-to-base64-encoded-string
    return "SHA-256="+btoa(String.fromCharCode(...new Uint8Array(param)));
  });
}


//see https://developer.mozilla.org/en-US/docs/Web/API/SubtleCrypto/importKey#pkcs_8_import
//TODO moved to rsa.js

function str2ab(str) {
  const buf = new ArrayBuffer(str.length);
  const bufView = new Uint8Array(buf);
  for (let i = 0, strLen = str.length; i < strLen; i++) {
    bufView[i] = str.charCodeAt(i);
  }
  return buf;
}

/**
 * Import a PEM encoded RSA private key, to use for RSA-PSS signing.
 * Takes a string containing the PEM encoded key, and returns a Promise
 * that will resolve to a CryptoKey representing the private key.
 */
function importPrivateKeyPromise(pem) {
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
 * Get a promise which will generate the signature header from date and digest field values
 * @param privateKey RSA private key
 * @param keyOwner unique identifier of the key pair, here is the URI of the actor which owns the key
 * @param date message creation date
 * @param digest digest of the activity which will be sent
 */
function signatureHeaderPromise(privateKey, keyOwner, date, digest){
	const te=new TextEncoder("UTF-8"); 
	const toBeSigned = "date: "+date+"\ndigest: "+digest;
	return window.crypto.subtle.sign(
		"RSASSA-PKCS1-v1_5",
	  	privateKey,
	  	te.encode(toBeSigned)).then((signature) => {
			const signatureBase64=btoa(String.fromCharCode(...new Uint8Array(signature)));
			return "keyId=\""+keyOwner+"\",algorithm=\"rsa-sha256\",headers=\"date digest\","+
				"signature=\""+signatureBase64+"\"";
	  	}) ;  	
}


