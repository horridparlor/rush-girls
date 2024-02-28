const FILTER_OPTIONS_STORAGE = 'filterOptions'
const FILTER_CHOICES_STORAGE = 'filtersChoices';
const API_ENDPOINT = '../api/';
const ADMIN_ENDPOINT = API_ENDPOINT + 'admin/';
const CARDS_PER_PAGE = 21;
const IMAGES_ENDPOINT = API_ENDPOINT + 'assets/';
const PNG = '.png';

const SELECTOR_EXPANSION = 'expansion-filter';
const SELECTOR_SEARCH_STRING = 'search-bar';
const SELECTOR_MIN_LEVEL = 'min-level';
const SELECTOR_MAX_LEVEL = 'max-level';
const SELECTOR_MIN_ATK = 'min-atk';
const SELECTOR_MAX_ATK = 'max-atk';
const SELECTOR_MIN_DEF = 'min-def';
const SELECTOR_MAX_DEF = 'max-def';
const SELECTOR_CLASS = 'class-selector';
const SELECTOR_DECK = 'deck-selector';
const SELECTOR_TYPE = 'card-type-selector';
const SELECTOR_SPECIAL = 'special-selector';
const SELECTOR_COST = 'cost-selector';
const SELECTOR_EFFECT = 'effect-selector';
const SELECTOR_LEGALITY = 'legality-selector';
const SELECTOR_SORT = 'sort-selector';
const SELECTOR_ORDER = 'order-selector';

let currentCardId;
let cards;
let cardsMap;
let currentPage;

function getExpansionIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    let expansionId = urlParams.get('expansion');
    if (!expansionId) {
        expansionId = '1';
        urlParams.set('expansion', expansionId);
        window.history.replaceState({}, '', `${window.location.pathname}?${urlParams}`);
    }
    return expansionId;
}

function showLoadingMessage(show) {
    const loadingMessage = document.getElementById('loading-message');
    if (show) {
        loadingMessage.style.display = 'block';
    } else {
        loadingMessage.style.display = 'none';
    }
}

function clearFilters() {
    const filterIds = [
        SELECTOR_EXPANSION,
        SELECTOR_SEARCH_STRING,
        SELECTOR_MIN_LEVEL,
        SELECTOR_MAX_LEVEL,
        SELECTOR_MIN_ATK,
        SELECTOR_MAX_ATK,
        SELECTOR_MIN_DEF,
        SELECTOR_MAX_DEF,
        SELECTOR_CLASS,
        SELECTOR_DECK,
        SELECTOR_TYPE,
        SELECTOR_SPECIAL,
        SELECTOR_COST,
        SELECTOR_EFFECT,
        SELECTOR_LEGALITY,
        SELECTOR_SORT,
        SELECTOR_ORDER,
    ];

    filterIds.forEach(id => {
        document.getElementById(id).value = '';
    });
}

function getDomValue(id) {
    return document.getElementById(id).value;
}

function getCards() {
    showLoadingMessage(true);
    const expansion = getDomValue(SELECTOR_EXPANSION);
    const searchString = getDomValue(SELECTOR_SEARCH_STRING).toLowerCase();
    const minLevel = getDomValue(SELECTOR_MIN_LEVEL);
    const maxLevel = getDomValue(SELECTOR_MAX_LEVEL);
    const minAtk = getDomValue(SELECTOR_MIN_ATK);
    const maxAtk = getDomValue(SELECTOR_MAX_ATK);
    const minDef = getDomValue(SELECTOR_MIN_DEF);
    const maxDef = getDomValue(SELECTOR_MAX_DEF);
    const cardClass = getDomValue(SELECTOR_CLASS);
    const cardType = getDomValue(SELECTOR_TYPE);
    const deck = getDomValue(SELECTOR_DECK);
    const special = getDomValue(SELECTOR_SPECIAL);
    const cost = getDomValue(SELECTOR_COST);
    const effect = getDomValue(SELECTOR_EFFECT);
    const legality = getDomValue(SELECTOR_LEGALITY);
    const sort = getDomValue(SELECTOR_SORT);
    const order = getDomValue(SELECTOR_ORDER);

    const filterChoices = {
      costType: cost,
      effectType: effect,
      expansion: expansion,
    };
    storeSession(FILTER_CHOICES_STORAGE, filterChoices);

    const rawParams = new URLSearchParams({
        expansionId: expansion,
        searchString: encodeURIComponent(searchString),
        minLevel: minLevel,
        maxLevel: maxLevel,
        minAtk: minAtk,
        maxAtk: maxAtk,
        minDef: minDef,
        maxDef: maxDef,
        classId: cardClass,
        cardTypeId: cardType,
        deckId: deck,
        specialId: special,
        costTypeId: cost,
        effectTypeId: effect,
        legalityId: legality,
        sortId: sort,
        orderId: order,
    });
    
    const urlParams = new URLSearchParams();
    rawParams.forEach((value, key) => {
        if (value !== null && value !== '') {
            urlParams.append(key, value);
        }
    });
    
    fetch(API_ENDPOINT + 'getCards.php?' + urlParams.toString())
        .then(response => response.json())
        .then(data => {
            showLoadingMessage(false);
            if (data.status === "No cards") {
                 const cardContainer = document.getElementById('card-container');
                cardContainer.innerHTML = '<div class="no-cards-found">No cards found</div>';
            } else {
                cards = data.cards;
                cardsMap = toMap(cards);
                currentPage = 0;
                displayCards();
            }
        })
        .catch(error => {
            console.error('Error fetching cards:', error);
        })
        .finally(() => {
            showLoadingMessage(false);
        });
}

function toMap(items) {
    const map = new Map();
    items.forEach(item => {
        if(item.id !== undefined) {
            map.set(item.id, item);
        }
    });
    return map;
}

function showModal(imageSrc, card) {
    const modal = document.getElementById("modal");
    const modalImg = document.getElementById("modal-image");
    const captionText = document.getElementById("modal-caption");
    modal.style.display = "block";
    modalImg.src = imageSrc;
    captionText.innerHTML = card.name;
    currentCardId = card.id;

    const span = document.getElementById("close-modal");

    span.onclick = function() {
        modal.style.display = "none";
    };

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
}

function storeSession(id, data) {
    sessionStorage.setItem(id, JSON.stringify(data));
}

function loadSession(id) {
    return JSON.parse(sessionStorage.getItem(id));
}

function displayCards() {
    const container = document.getElementById('card-container');
    container.innerHTML = '';
    const startIndex = currentPage * CARDS_PER_PAGE
    cardsOnPage = cards.slice(startIndex, startIndex + CARDS_PER_PAGE );
    cardsOnPage.forEach(card => {
        const cardDiv = document.createElement('div');
        cardDiv.className = 'card';
        cardDiv.title = card.name;
        const img = new Image();
        img.id = `card-image-${card.id}`;
        img.alt = card.name;
        cardDiv.appendChild(img);

        cardDiv.onclick = () => {
            showModal(img.src, card);
        };

        container.appendChild(cardDiv);
        updateCardImage(card.id);
    });
}

function updateCardImage(id) {
    const img = document.getElementById(`card-image-${id}`);
    img.src = IMAGES_ENDPOINT + getCardImagePath(id) + PNG;
}

function getCardImagePath(id) {
    const card = cardsMap.get(id);
    return formatString(card.expansion) + '/' + formatString(card.name);
}

function formatString(name) {
    return name
        .replace(/-/g, ' ')
        .replace(/[^a-zA-Z0-9\s]/g, '')
        .split(' ')
        .map((word, index) =>
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        )
        .join('');
}

function modalInit() {
    const modal = document.getElementById('modal');
    const changeImageButton = document.getElementById('change-image-button');
    const imageInput = document.getElementById('image-input');

    changeImageButton.addEventListener('click', () => {
        imageInput.click();
    });

    imageInput.addEventListener('change', async (event) => {
        if (event.target.files && event.target.files[0]) {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = async (e) => {
                const imageData = e.target.result;
                const modalImage = document.getElementById('modal-image');
                modalImage.src = imageData;

                await updateImageOnServer(currentCardId, imageData);
            };

            reader.readAsDataURL(file);
        }
    });
}

async function updateImageOnServer(cardId, imageData) {
    const formData = new FormData();
    formData.append('cardId', cardId);
    const imageBlob = dataURLtoBlob(imageData);
    formData.append('imageData', imageBlob);

    try {
        const response = await fetch(ADMIN_ENDPOINT + 'updateImage.php', {
            method: 'POST',
            body: formData
        });
        if (!response.ok) {
            showToast(`HTTP error uploading image! status: ${response.status}`);
        } else {
            showToast('Image updated successfully');
            updateCardImage(cardId);
        }
    } catch (error) {
        showToast('Error updating image: ' + error);
    }
}

function showToast(message, duration = 3000) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.className = 'toast';
    document.body.appendChild(toast);

    setTimeout(() => {
        document.body.removeChild(toast);
    }, duration);
}

function dataURLtoBlob(dataURL) {
    const byteString = atob(dataURL.split(',')[1]);
    const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];

    const ab = new ArrayBuffer(byteString.length);
    const ia = new Uint8Array(ab);
    for (let i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }

    return new Blob([ab], {type: mimeString});
}

function pageArrowsInit() {
    document.getElementById('prev-page').addEventListener('click', prevPage);
    document.getElementById('next-page').addEventListener('click', nextPage);
}

function prevPage() {
    if (currentPage > 0) {
        currentPage -= 1;
        displayCards();
    }
}

function nextPage() {
    if (cards.length > (currentPage + 1) * CARDS_PER_PAGE) {
        currentPage += 1;
        displayCards();
    }
}

const fetchFilters = async () => {
    const filters = loadSession(FILTER_OPTIONS_STORAGE);
    if (filters) {
        updateFilters(filters);
        return;
    }
    try {
        const response = await fetch(API_ENDPOINT + 'getFilters.php');
        if (!response.ok) {
            showToast("Error fetching filters");
        }
        const data = await response.json();
        sessionStorage.setItem(FILTER_OPTIONS_STORAGE, JSON.stringify(data));
        updateFilters(data);
    } catch (error) {
        showToast("Cannot fetch filters");
    }
};

function updateFilters(data) {
    const costTypes = data.costTypes;
    const effectTypes = data.effectTypes;
    const expansions = data.expansions;
    const filterChoices = loadSession(FILTER_CHOICES_STORAGE);

    costTypes.forEach(({id, name}) => {
        addOption(id, name, SELECTOR_COST);
    });
    effectTypes.forEach(({id, name}) => {
        addOption(id, name, SELECTOR_EFFECT);
    });
    expansions.forEach(({id, name}) => {
       addOption(id, name, SELECTOR_EXPANSION);
    });

    if (filterChoices) {
        setFilter(SELECTOR_COST, filterChoices.costType);
        setFilter(SELECTOR_EFFECT, filterChoices.effectType);
        setFilter(SELECTOR_EXPANSION, filterChoices.expansion);
    }
    getCards();
}

function setFilter(id, value) {
    document.getElementById(id).value = value;
}

function addOption(value, message, parentId) {
    const option = document.createElement('option');
    option.value = value;
    option.textContent = message;
    document.getElementById(parentId).appendChild(option);
}

window.onload = () => {
    document.getElementById('clear-button').addEventListener('click', clearFilters);
    document.getElementById('search-button').addEventListener('click', getCards);
    modalInit();
    pageArrowsInit();
    fetchFilters();
};
