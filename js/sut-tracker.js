let search = document.getElementById('search_box');
let results = document.getElementById('results');
let baseUrl = window.wpApiSettings.root;
let nonce = window.wpApiSettings.nonce;
let gaParam = document.getElementById('ga_parameter_box');
let userParamSelector = document.getElementById('add-param');
let userGaSelector = document.getElementById('add-ga-param');
let userNameSelector = document.getElementById('add-name-param');
let userFavoriteSelector = document.getElementById('add-favorite-param');
let userCampaignSelector = document.getElementById('add-campaign-param');
let productAttributsSelector = document.getElementById('param-product');
let productAttributsChoice = document.getElementById('add-varia-param-request');
let productAttributs = document.getElementById('param-product-list');
let attributsBlock = document.getElementById('all-param');
let finalUrl = document.getElementById('final-url');
let urlResponse = document.getElementById('url-response');
let divResponse = document.getElementById('link-url');
let blockParam = document.getElementById('param-content');
let btnSubmit = document.getElementById('generate-url');
let date = new Date();

if (document.getElementById('search-cleaner')) {
    document.getElementById('search-cleaner').addEventListener('click', function () {
        document.getElementById('search_box').value = "";
        results.classList.remove('active');
        if (blockParam) {
            blockParam.classList.remove('active');
        }
        if (gaParam){
            gaParam.classList.remove('active');
        }
        finalUrl.classList.remove('active');
        divResponse.classList.remove('active');
        results.innerHTML = "";
    })
}
if (search) {
    const input = document.querySelector('#search_box');
    let timer;
    const waitTime = 500;

    const search = (text) => {
            divResponse.classList.remove('active');
            let url = baseUrl + midUrl + 'search=' + input.value + '&per_page=20&_locale=user';
            let xhr = new XMLHttpRequest();
            xhr.open('GET', url);
            xhr.setRequestHeader("X-Wp-Nonce", nonce);
            xhr.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let data = JSON.parse(this.responseText);
                    showResult(searchType, data);
                    document.getElementById('search-cleaner').classList.add('active');
                    document.getElementById('loader-search').classList.remove('active');
                }
            }
            xhr.send();
    };

    input.addEventListener('keyup', (e) => {
        const text = search.value;
        document.getElementById('search-cleaner').classList.remove('active');
        document.getElementById('loader-search').classList.add('active');

        clearTimeout(timer);

        timer = setTimeout(() => {
            search(text);
        }, waitTime);

        if (document.getElementById('button-action') != null){
            document.getElementById('button-action').outerHTML = "";
        }
    })
}
if (document.getElementsByClassName('select-type')) {
    document.querySelectorAll('.select-type').forEach(item => {
        item.addEventListener('click', event => {
            if (document.getElementById('url-shorted').innerText !== "") {
                cleanResult();
            }
            document.querySelectorAll('.select-type').forEach(line => {
                line.classList.remove('active');
            })
            document.getElementById('results').classList.remove('active');
            document.getElementById('search_box').value = "";
            document.getElementById('form-group').classList.add('active');
            if (item.classList.contains('active')){
                item.classList.remove('active');
            } else {
                item.classList.add('active');
            }
        })
    })
}
function showResult(type, data){
    let i = 0;
    results.innerHTML = "";
    if (data.length !== 0) {
        results.classList.add('active');
        switch (type) {
            case 'post':
                data.forEach(element => {
                    if (element.title.toLowerCase().includes(search.value.toLowerCase())) {
                        results.insertAdjacentHTML('afterbegin', '<button onclick="selectSearch(\'' + element.subtype + ',' + element.id + '\')" class="result-line" id="line-0-' + i + '"><span class="search-header"><span class="search-title">' + element.title + '</span><span class="search-url">' + element.url + '</span></span><span class="search-type">' + element.subtype + '</span></button>');
                        i++;
                    }
                })
                break;
            case 'category':
                data.forEach(element => {
                    if (element.title.toLowerCase().includes(search.value.toLowerCase())) {
                        results.insertAdjacentHTML('afterbegin', '<button onclick="selectSearch(\'' + element.type + ',' + element.id + '\')" class="result-line" id="line-0-' + i + '"><span class="search-header"><span class="search-title">' + element.title + '</span><span class="search-url">' + element.url + '</span></span><span class="search-type">' + element.type + '</span></button>');
                        i++;
                    }
                })
                break;
            default:
                alert('Please Choose a Type');
            }
        results.insertAdjacentHTML('beforeend', '<button onclick="showParam()" class="result-line" id="line-0-1"><span class="search-header"><span class="search-title">Custom Url</span><span class="search-url">' + search.value + '</span></span><span class="search-type">Custom Url</span></button>');
    } else {
        results.classList.add('active');
        results.insertAdjacentHTML('afterbegin', '<button class="result-line" id="line-0-0"><span class="search-header"><span class="search-title">No Results</span><span class="search-url">Change your request or select a custom Url</span></span><span class="search-type">Not Found</span></button>');
        results.insertAdjacentHTML('afterbegin', '<button onclick="showParam()" class="result-line" id="line-0-1"><span class="search-header"><span class="search-title">Custom Url</span><span class="search-url">' + search.value + '</span></span><span class="search-type">Custom Url</span></button>');
    }
}

function selectSearch(type) {
    if (type.length !== 0) {
        let valueArray = type.split(',');
        let urlElement;
        if (valueArray[0] === 'product') {
            urlElement = baseUrl + 'wp/v2/' + valueArray[0] + '/' + valueArray[1];
        } else if (valueArray[0] === 'page' || valueArray[0] === 'post') {
            urlElement = baseUrl + 'wp/v2/' + valueArray[0] + 's/' + valueArray[1];
        } else if (valueArray[0] === 'product_cat') {
            urlElement = baseUrl + 'wp/v2/product_cat/' + valueArray[1];
        } else if (valueArray[0] === 'category') {
            urlElement = baseUrl + 'wp/v2/categories/' + valueArray[1];
        } else {
            urlElement = baseUrl + 'wp/v2/'+valueArray[0]+'/' + valueArray[1];
        }
        let xhr = new XMLHttpRequest();
        xhr.open('GET', urlElement, true);
        xhr.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let data = JSON.parse(this.responseText);
                search.value = data.link;
                results.classList.remove('active');
                document.getElementById('param-form-content').classList.add('active');
                finalUrl.classList.add('active');
                if (valueArray[0] === 'product' && data.variation_products.length !== 0) {
                    productAttributsSelector.classList.add('active');
                    checkVariations(data.variation_products);
                } else {
                    productAttributsSelector.classList.remove('active');
                }

            }
        };
        xhr.send();
    }
}

function showParam() {
    divResponse.classList.remove('active');
    results.classList.remove('active');
    document.getElementById('param-form-content').classList.add('active');
    finalUrl.classList.add('active');
}

function checkVariations(variations){
    let option = '<select name="product-attributs" id="select_attributs">';
    for(let i=0; i<variations.length; i++){
        let optionValue = "";
        let optionTxt = "| ";
        if (variations[i].is_in_stock === true) {
            let singleAttrib = variations[i].attributes;
            for (let attrib in singleAttrib) {
                optionValue += '|' +attrib + '|'+singleAttrib[attrib];
                optionTxt = optionTxt + singleAttrib[attrib] + ' | ';
            }
            optionValue = optionValue.substring(1);
            option = option + '<option value="'+optionValue+'">' + optionTxt + '</option>';
        }
    }
    option = option + '</select>';
    productAttributs.innerHTML = option;
}

function generateUrl(){
    btnSubmit.disabled = true;
    if (document.getElementById('button-action') != null){
        document.getElementById('button-action').outerHTML = "";
    }
    let url = search.value;
    if (url.length === 0) {
        alert('Url must not be empty');
    }
    let param = [];
    if (productAttributsChoice.checked) {
        e = document.getElementById('select_attributs');
        let attributChoice = e.options[e.selectedIndex].value;
        let choiceElement = attributChoice.split('|');
        for (let j=0; j < choiceElement.length; j++) {
            param.push(
                {
                    'key': cleanInput(choiceElement[j]),
                    'value': cleanInput(choiceElement[j+1])
                }
            )
            j++;
        }

    }
    if (userParamSelector.checked) {
        let userParam = document.getElementsByClassName('param-element');
        if (userParam.length > 0) {
            for (let i = 0; i <userParam.length; i++) {
                let children = userParam[i].children;
                if (children.key.value !== "" && children.value.value !== "") {
                    param.push(
                        {
                            'key': cleanInput(children.key.value),
                            'value': cleanInput(children.value.value)
                        }
                    )
                }

            }
        }
    }
    if (userGaSelector.checked) {
        gaParam = document.getElementsByClassName('param-element-ga');
        if (gaParam.length > 0) {
            for (let i = 0; i <gaParam.length; i++) {
                let children = gaParam[i].children;
                if (children.value.value !== "") {
                    param.push(
                        {
                            'key': cleanInput(children.key.value),
                            'value': cleanInput(children.value.value)
                        }
                    ) 
                }

            }
        }
    }
    let linkName = "";
    let campaignId = "";
    let isFavorite = false;
    if (userFavoriteSelector.checked) {
        isFavorite = true;
    }
    if (userNameSelector.checked) {
        if (document.getElementById('url-name').value.length > 0) {
            linkName = document.getElementById('url-name').value;
        }
    }
    if (userCampaignSelector.checked) {
        let selectCampaign = document.getElementById('campaign-choice');
        campaignId = selectCampaign.options[selectCampaign.selectedIndex].value;
    }
    document.getElementById('loader-generate').classList.add('active');
    generateLinkAjax(url, param, linkName, isFavorite, campaignId);
}

function cleanInput(val) {
    val = val.trim();
    val = val.replace(/ /g, '_');
    return encodeURIComponent(val);
}
if (urlResponse) {
    urlResponse.addEventListener('click', execCopy());
}
function execCopy(element) {
    if (element) {
        document.getElementById('button-copy-short').classList.add('copied');
        document.getElementById('button-copy-short').innerHTML = wpApiSettings.copiedShort;
        let textToCopy = document.getElementById(element).innerText;
        const el = document.createElement('textarea');
        el.value = textToCopy;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        setTimeout(function(){
            document.getElementById('button-copy-short').classList.remove('copied');
            document.getElementById('button-copy-short').innerHTML = wpApiSettings.copyShort;
        }, 2000);

    }
}
function generateSocialShareBlock(url){
    let socialList = '<ul>';
    socialList += '<li><a href="https://www.facebook.com/sharer/sharer.php?u='+url+'" target="_blank">'+wpApiSettings.imgSocial.facebook+'</a></li>';
    socialList += '<li><a href="https://twitter.com/intent/tweet?url='+url+'" target="_blank">'+wpApiSettings.imgSocial.twitter+'</a></li>';
    socialList += '<li><a href="https://www.linkedin.com/shareArticle?mini=true&url='+url+'" target="_blank">'+wpApiSettings.imgSocial.linkedin+'</a></li>';
    socialList += '<li><a href="mailto:info@example.com?body='+url+'" target="_blank">'+wpApiSettings.imgSocial.mail+'</a></li>';
    socialList += '</ul>';
    document.getElementById('social-list').innerHTML = socialList;
}

function cleanResult() {
    productAttributs.innerHTML = "";
    attributsBlock.classList.remove('active');
    document.getElementById('url-shorted').innerHTML = "";
    document.getElementById('url-response').innerHTML = "";
    divResponse.classList.remove('active');
}

function generateLinkAjax(value, param = "", name = "", favorite = false, campaignId = "") {
    divResponse.classList.remove('active');
    btnSubmit.classList.add('active');
    const json = { "url": value, "param": param, "name": name, "isFavorite": favorite, "campaignId": campaignId};
    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : ajaxurl,
        data : {action: "sut_byw_create_short_link_ajax", data: {'json': json, 'nonce': nonce}},
        success: function(response) {
            if(response.success === true) {
                document.getElementById('loader-generate').classList.remove('active');
                btnSubmit.classList.remove('active');
                let finalLink = response.data.url;
                let shorted = response.data.token;
                let qrcode = response.data.qrcode;
                document.getElementById('url-response').innerHTML = finalLink;
                document.getElementById('url-shorted').innerHTML = shorted;
                divResponse.classList.add('active');
                document.getElementById('button-copy').innerHTML = '<div class="button-action" id="button-action"><button onmouseover="modifyCss(\'url-shorted\')" id="button-copy-short" class="button-copy-short" onclick="execCopy(\'' + 'url-shorted' + '\')" type="submit">'+wpApiSettings.copyShort+'</button></div>';
                if (response.data.qrcode.success === true) {
                    document.getElementById('qrcode-element').innerHTML = '<h2 class="title-link">QRcode generated</h2><div class="qrcode-button" id="qrcode-button"><img alt="qrcode" src="'+response.data.qrcode.formats.small+'" /></div>';
                } else if (response.data.qrcode.success === false) {
                    document.getElementById('qrcode-element').innerHTML = '<h2 class="title-link">'+response.data.qrcode.message+'</h2>';
                } else {
                    document.getElementById('qrcode-element').innerHTML = '<h2 class="title-link">Error while generating QRcode</h2>';
                }
                generateSocialShareBlock(response.data.token);
            }
            else if (response.success === false) {
                document.getElementById('loader-generate').classList.remove('active');
                btnSubmit.classList.remove('active');
                btnSubmit.classList.add('blocked-plugin');
                document.getElementById('btn-txt').innerText = response.data.message;
                setTimeout(function() {
                    btnSubmit.classList.remove('blocked-plugin');
                    btnSubmit.classList.add('active');
                    document.getElementById('btn-txt').innerText = 'Generate Shortlink';
                }, 2000);
            }
            else {
                alert("An error occurred, Please try again");
            }
        }
    });
    btnSubmit.disabled = false;
}

function modifyCss(element) {
    document.getElementById(element).style.borderWidth = "2px";
    setTimeout(function() {
        document.getElementById(element).style.borderWidth = "1px";
    }, 2000);
}

function manageType() {
    //this.classList.toogle('active');
    let id = this.id;
    let searchType = "";
    document.getElementById('search_box').value = "";
    let campaign = document.querySelector('#add-campaign-param');
    if (campaign.checked === true) {
        document.getElementById('param-campaign-request').classList.add('active');
    }
    document.getElementById('results').classList.remove('active');
    if (id === 'type-category') {
        document.getElementById('type-product').classList.remove('active');
        document.getElementById('type-category').classList.add('active');
        document.getElementById("search-category").checked = true;
        document.getElementById('add-varia-param-request').checked = false;
        document.getElementById('param-product').classList.remove('active');
        document.getElementById('param-product-list').classList.remove('active');
        window.midUrl = 'wp/v2/search?orderby=title&type=term&';
        window.searchType = 'category';
    } else {
        document.getElementById('type-category').classList.remove('active');
        document.getElementById('type-product').classList.add('active');
        document.getElementById("search-product").checked = true;
        window.midUrl = 'wp/v2/search?orderby=title&type=post&';
        window.searchType = 'post';
    }
    document.getElementById('search-element').classList.add('active');
}

if (document.getElementById('first-step') ) {
    document.getElementById('type-category').addEventListener('click', manageType);
    document.getElementById('type-product').addEventListener('click', manageType);

}
if (document.getElementById('add-param-request') ) {
    document.getElementById('add-param').addEventListener('change', function() {
        document.getElementById('param-list').classList.toggle("active");
    })
}
if (document.getElementById('add-ga-param-request') ) {
    document.getElementById('add-ga-param').addEventListener('change', function() {
        document.getElementById('param-ga-list').classList.toggle("active");
    })
}
if (document.getElementById('add-campaign-param-request') ) {
    document.getElementById('add-campaign-param').addEventListener('change', function() {
        document.getElementById('param-campaign-request').classList.toggle("active");
    })
}
if (document.getElementById('add-name-param-request') ) {
    document.getElementById('add-name-param').addEventListener('change', function() {
        document.getElementById('param-name-request').classList.toggle("active");
    })
}
if (document.getElementById('add-param-product') ) {
    document.getElementById('add-varia-param-request').addEventListener('change', function() {
        document.getElementById('param-product-list').classList.toggle("active");
    })
}

function addParam() {
    id = document.getElementsByClassName('param-element').length;
    paramContent = '<div id="param-'+id+'" class="param-element"><input type="text" name="key" placeholder="key" class="custom-param"><input type="text" name="value" placeholder="value"  class="custom-param-value"><button onclick="addParam()"  class="add-custom-param">'+wpApiSettings.add+'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button><button onclick="deleteParam(\'param-'+id+'\')"  class="delete-custom-param">'+wpApiSettings.remove+'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>';
    document.getElementById('param-list').insertAdjacentHTML("beforeend",paramContent);
}

function deleteParam(event) {
    document.getElementById(event).outerHTML = "";
}
