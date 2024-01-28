const IMAGES_STORAGE = 'cardImages';
const API_ENDPOINT = '../api/rush-girls/';
const ADMIN_ENDPOINT = API_ENDPOINT + 'admin/';

let currentCardId;

function getExpansionIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    let expansionId = urlParams.get('expansion');
    if (!expansionId) {
        expansionId = '1';
        // Optional: Update the URL to include the default expansion parameter
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
        'expansion-filter',
        'search-bar',
        'min-level',
        'max-level',
        'min-atk',
        'max-atk',
        'min-def',
        'max-def',
        'class-selector',
        'card-type-selector',
        'is-ace-selector',
        'cost-selector',
        'effect-selector',
        'legality-selector'
    ];

    filterIds.forEach(id => {
        document.getElementById(id).value = '';
    });
}

function getCards() {
    showLoadingMessage(true);
    const expansion = document.getElementById('expansion-filter').value;
    const searchString = document.getElementById('search-bar').value.toLowerCase();
    const minLevel = document.getElementById('min-level').value;
    const maxLevel = document.getElementById('max-level').value;
    const minAtk = document.getElementById('min-atk').value;
    const maxAtk = document.getElementById('max-atk').value;
    const minDef = document.getElementById('min-def').value;
    const maxDef = document.getElementById('max-def').value;
    const cardClass = document.getElementById('class-selector').value;
    const cardType = document.getElementById('card-type-selector').value;
    const isAce = document.getElementById('is-ace-selector').value;
    const cost = document.getElementById('cost-selector').value;
    const effect = document.getElementById('effect-selector').value;
    const legality = document.getElementById('legality-selector').value;

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
        isAce: isAce,
        costTypeId: cost,
        effectTypeId: effect,
        legalityId: legality
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
                displayCards(data.cards);
            }
        })
        .catch(error => {
            console.error('Error fetching cards:', error);
        })
        .finally(() => {
            showLoadingMessage(false);
        });
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

function displayCards(cards) {
    const container = document.getElementById('card-container');
    container.innerHTML = '';
    if (!sessionStorage.getItem(IMAGES_STORAGE)) {
        sessionStorage.setItem(IMAGES_STORAGE, JSON.stringify({}));
    }
    cards.forEach(card => {
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
    const cardImages = JSON.parse(sessionStorage.getItem(IMAGES_STORAGE));
    const img = document.getElementById(`card-image-${id}`);

    if (cardImages[id]) {
        img.src = cardImages[id];
    } else {
        getCardImage(id).then(data => {
            const imageData = 'data:image/jpeg;base64,' + data;
            img.src = imageData;

            cardImages[id] = imageData;
            sessionStorage.setItem(IMAGES_STORAGE, JSON.stringify(cardImages));
        });
    }
}

function getCardImage(cardId) {
    return fetch(API_ENDPOINT + `getCardImage.php?cardId=${cardId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network failure.');
            }
            return response.json();
        })
        .then(data => {
            return data.imageData;
        })
        .catch(error => {
            console.error('Error fetching card image:', error);
        });
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

window.onload = () => {
    document.getElementById('clear-button').addEventListener('click', clearFilters);
    document.getElementById('search-button').addEventListener('click', getCards);
    modalInit();
    getCards();
};
