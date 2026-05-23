window.CapellFrontendAuthoring = window.CapellFrontendAuthoring || (function ()
{ let editMode = false; function translated(key) { const labels = {
inlineEditor:
@json(__('capell-frontend-authoring::authoring.inline_editor'))
, editMode:
@json(__('capell-frontend-authoring::authoring.edit_mode'))
, adminEditing:
@json(__('capell-frontend-authoring::authoring.admin_editing'))
, cached:
@json(__('capell-frontend-authoring::authoring.cached'))
, close:
@json(__('capell-frontend-authoring::authoring.close'))
, editPage:
@json(__('capell-frontend-authoring::authoring.edit_page'))
, editPageActive:
@json(__('capell-frontend-authoring::authoring.edit_page_active'))
, notCached:
@json(__('capell-frontend-authoring::authoring.not_cached'))
, toggleEditMode:
@json(__('capell-frontend-authoring::authoring.toggle_edit_mode'))
, updated:
@json(__('capell-frontend-authoring::authoring.updated'))
, updatedBy:
@json(__('capell-frontend-authoring::authoring.updated_by'))
, }; return labels[key] || key; } const bannerContext =
@json($banner)
; let modalRestoreTarget = null; let modalKeydownHandler = null; function
escapeHtml(value) { return String(value || '').replace(/[&<>"']/g, (character)
=> ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
}[character])); } function focusableElements(root) { return
Array.from(root.querySelectorAll( 'button, iframe, a[href], input, select,
textarea, [tabindex]:not([tabindex="-1"])' )).filter((element) => typeof
element.focus === 'function' && ! element.hasAttribute('disabled') &&
element.getAttribute('aria-hidden') !== 'true'); } function
appendToolbarText(parent, className, text, title) { if (! text) { return null; }
const element = document.createElement('span'); element.className = className;
element.textContent = String(text); if (title) { element.setAttribute('title',
String(title)); } parent.appendChild(element); return element; } function
closeModal() { document .querySelectorAll('.capell-authoring-modal')
.forEach((modal) => modal.remove()); if (modalKeydownHandler) {
document.removeEventListener('keydown', modalKeydownHandler);
modalKeydownHandler = null; } const restoreTarget = modalRestoreTarget;
modalRestoreTarget = null; const fallbackTarget =
document.querySelector('.capell-authoring-toolbar__toggle'); if (restoreTarget
instanceof HTMLElement && restoreTarget.isConnected) { restoreTarget.focus({
preventScroll: true }); return; } if (fallbackTarget instanceof HTMLElement) {
fallbackTarget.focus({ preventScroll: true }); } } function openModal(region) {
closeModal(); modalRestoreTarget = document.activeElement instanceof HTMLElement
? document.activeElement : null; const overlay = document.createElement('div');
overlay.className = 'capell-authoring-modal'; const regionLabel =
escapeHtml(region.label); const editUrl = escapeHtml(region.edit_url);
overlay.innerHTML = `
<div class="capell-authoring-modal__backdrop"></div>
<div
    class="capell-authoring-modal__panel"
    role="dialog"
    aria-modal="true"
    aria-label="${regionLabel}"
>
    <button
        class="capell-authoring-modal__close"
        type="button"
        aria-label="${escapeHtml(translated('close'))}"
    >
        x
    </button>
    <iframe
        class="capell-authoring-modal__frame"
        src="${editUrl}"
        title="${regionLabel}"
    ></iframe>
</div>
`; document.body.appendChild(overlay); const panel =
overlay.querySelector('.capell-authoring-modal__panel'); const closeButton =
overlay.querySelector('.capell-authoring-modal__close'); modalKeydownHandler =
(event) => { if (event.key === 'Escape') { event.preventDefault(); closeModal();
return; } if (event.key !== 'Tab' || ! panel) { return; } const focusable =
focusableElements(panel); if (focusable.length === 0) { return; } const first =
focusable[0]; const last = focusable[focusable.length - 1]; if (event.shiftKey
&& document.activeElement === first) { event.preventDefault(); last.focus();
return; } if (! event.shiftKey && document.activeElement === last) {
event.preventDefault(); first.focus(); } }; document.addEventListener('keydown',
modalKeydownHandler); closeButton .addEventListener('click', closeModal);
overlay .querySelector('.capell-authoring-modal__backdrop')
.addEventListener('click', closeModal); const frame =
overlay.querySelector('.capell-authoring-modal__frame');
frame.addEventListener('load', () => { try { const frameDocument =
frame.contentDocument; if (! frameDocument) { return; }
frameDocument.addEventListener('keydown', (event) => { if (event.key ===
'Escape') { event.preventDefault(); closeModal(); return; } if (event.key !==
'Tab') { return; } const focusable = focusableElements(frameDocument); if
(focusable.length === 0) { event.preventDefault(); closeButton.focus({
preventScroll: true }); return; } const first = focusable[0]; const last =
focusable[focusable.length - 1]; if (event.shiftKey &&
frameDocument.activeElement === first) { event.preventDefault();
closeButton.focus({ preventScroll: true }); return; } if (! event.shiftKey &&
frameDocument.activeElement === last) { event.preventDefault();
closeButton.focus({ preventScroll: true }); } }); } catch (error) {} });
closeButton.focus({ preventScroll: true }); } function ensureStyles() { if
(document.getElementById('capell-authoring-styles')) { return; } const style =
document.createElement('style'); style.id = 'capell-authoring-styles';
style.textContent = ` .capell-authoring-region { outline: 2px dashed rgba(37,
99, 235, .65); outline-offset: 4px; position: relative; }
.capell-authoring-toolbar { align-items: center; background: #111827;
border-top: 1px solid rgba(255, 255, 255, .14); box-shadow: 0 -16px 48px
rgba(15, 23, 42, .28); box-sizing: border-box; color: #fff; display: grid; font:
600 13px/1.35 ui-sans-serif, system-ui, sans-serif; gap: 12px;
grid-template-columns: minmax(0, 1fr) auto; left: 0; padding: 12px max(16px,
env(safe-area-inset-right)) calc(12px + env(safe-area-inset-bottom)) max(16px,
env(safe-area-inset-left)); position: fixed; right: 0; bottom:
var(--capell-authoring-bottom-offset, 0px); width: 100%; z-index: 2147483000; }
.capell-authoring-toolbar__content { align-items: center; display: flex;
flex-wrap: wrap; gap: 8px 14px; min-width: 0; } .capell-authoring-toolbar__label
{ font: 800 13px/1 ui-sans-serif, system-ui, sans-serif; letter-spacing: .02em;
text-transform: uppercase; white-space: nowrap; }
.capell-authoring-toolbar__page { font-weight: 800; max-width: min(42ch, 100%);
min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.capell-authoring-toolbar__meta { color: #d1d5db; font-weight: 600; max-width:
min(48ch, 100%); min-width: 0; overflow: hidden; text-overflow: ellipsis;
white-space: nowrap; } .capell-authoring-toolbar__status { align-items: center;
background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .16);
border-radius: 999px; color: #f9fafb; display: inline-flex; font: 800 11px/1
ui-sans-serif, system-ui, sans-serif; padding: 5px 8px; text-transform:
uppercase; white-space: nowrap; } .capell-authoring-toolbar__actions {
align-items: center; display: flex; gap: 8px; justify-content: flex-end; }
.capell-authoring-toolbar__toggle { align-items: center; background: #fff;
border: 0; border-radius: 999px; color: #111827; cursor: pointer; display:
inline-flex; font: 700 12px/1 ui-sans-serif, system-ui, sans-serif; gap: 6px;
padding: 8px 11px; } .capell-authoring-toolbar__toggle[aria-pressed="true"] {
background: #2563eb; color: #fff; } .capell-authoring-button { background:
#111827; border: 0; border-radius: 999px; color: #fff; cursor: pointer; font:
600 12px/1 ui-sans-serif, system-ui, sans-serif; padding: 7px 10px; position:
absolute; right: 0; top: 0; transform: translateY(calc(-100% - 6px)); opacity:
0; pointer-events: none; transition: opacity .12s ease, transform .12s ease;
z-index: 2147482999; } .capell-authoring-editing .capell-authoring-button,
.capell-authoring-region:hover > .capell-authoring-button,
.capell-authoring-region:focus-within > .capell-authoring-button,
.capell-authoring-button:focus { opacity: 1; pointer-events: auto; }
.capell-authoring-modal { inset: 0; position: fixed; z-index: 2147483001; }
.capell-authoring-modal__backdrop { background: rgba(17, 24, 39, .55); inset: 0;
position: absolute; } .capell-authoring-modal__panel { background: #fff;
border-radius: 12px; box-shadow: 0 24px 80px rgba(15, 23, 42, .3); height:
min(560px, calc(100vh - 32px)); left: 50%; max-width: calc(100vw - 32px);
overflow: hidden; position: absolute; top: 50%; transform: translate(-50%,
-50%); width: min(760px, calc(100vw - 32px)); } .capell-authoring-modal__close {
align-items: center; background: #111827; border: 0; border-radius: 999px;
color: #fff; cursor: pointer; display: flex; font: 20px/1 ui-sans-serif,
system-ui, sans-serif; height: 32px; justify-content: center; position:
absolute; right: 10px; top: 10px; width: 32px; z-index: 2; }
.capell-authoring-modal__frame { border: 0; height: 100%; width: 100%; } `;
document.head.appendChild(style); } function ensureToolbar() { if
(document.getElementById('capell-authoring-toolbar')) { return; } const toolbar
= document.createElement('div'); toolbar.id = 'capell-authoring-toolbar';
toolbar.className = 'capell-authoring-toolbar'; toolbar.setAttribute('role',
'region'); toolbar.setAttribute('aria-label', translated('adminEditing')); const
updatedMeta = bannerContext.updated ? `${translated('updated')}
${bannerContext.updated}` : ''; const updatedByMeta = bannerContext.updated_by ?
`${translated('updatedBy')} ${bannerContext.updated_by}` : ''; const cacheStatus
= bannerContext.html_cached ? translated('cached') : translated('notCached');
const content = document.createElement('div'); content.className =
'capell-authoring-toolbar__content'; appendToolbarText(content,
'capell-authoring-toolbar__label', translated('adminEditing'));
appendToolbarText(content, 'capell-authoring-toolbar__page', bannerContext.page,
bannerContext.page); appendToolbarText(content,
'capell-authoring-toolbar__meta', bannerContext.url); appendToolbarText(content,
'capell-authoring-toolbar__meta', updatedMeta); appendToolbarText(content,
'capell-authoring-toolbar__meta', updatedByMeta); const status =
appendToolbarText(content, 'capell-authoring-toolbar__status', cacheStatus);
status.setAttribute('aria-live', 'polite'); const actions =
document.createElement('div'); actions.className =
'capell-authoring-toolbar__actions'; const toggle =
document.createElement('button'); toggle.className =
'capell-authoring-toolbar__toggle'; toggle.type = 'button';
toggle.setAttribute('aria-pressed', 'false'); toggle.setAttribute('aria-label',
translated('toggleEditMode')); toggle.textContent = translated('editPage');
actions.appendChild(toggle); toolbar.appendChild(content);
toolbar.appendChild(actions); toggle .addEventListener('click', () => { editMode
= ! editMode;
document.documentElement.classList.toggle('capell-authoring-editing', editMode);
toggle.setAttribute('aria-pressed', editMode ? 'true' : 'false');
toggle.textContent = editMode ? translated('editPageActive') :
translated('editPage'); }); document.body.appendChild(toolbar);
updateBottomOffset(); window.addEventListener('resize',
scheduleBottomOffsetUpdate, { passive: true }); } let bottomOffsetFrame = null;
function scheduleBottomOffsetUpdate() { if (bottomOffsetFrame !== null) {
return; } bottomOffsetFrame = window.requestAnimationFrame(() => {
bottomOffsetFrame = null; updateBottomOffset(); }); } function
updateBottomOffset() { const debugBars = ['#phpdebugbar', '.phpdebugbar',
'[id^="debugbar"]'].map((selector) =>
document.querySelector(selector)).filter(Boolean); const debugBarHeight =
debugBars.reduce((height, element) => { const rect =
element.getBoundingClientRect(); const style = window.getComputedStyle(element);
const isBottomBar = style.position === 'fixed' && rect.bottom >=
window.innerHeight - 4 && rect.height > 0; return isBottomBar ? Math.max(height,
rect.height) : height; }, 0);
document.documentElement.style.setProperty('--capell-authoring-bottom-offset',
`${debugBarHeight}px`); } function clearRegions() { document
.querySelectorAll('.capell-authoring-button') .forEach((button) =>
button.remove()); document .querySelectorAll('.capell-authoring-region')
.forEach((element) => element.classList.remove('capell-authoring-region')); }
function renderRegions(regions) { clearRegions(); if (! regions || typeof
regions !== 'object') { return; } ensureStyles(); ensureToolbar();
Object.values(regions).forEach((region) => { const target =
document.querySelector(region.selector); if (! target ||
target.dataset.capellAuthoringRegion === region.id) { return; }
target.dataset.capellAuthoringRegion = region.id;
target.classList.add('capell-authoring-region'); const button =
document.createElement('button'); const buttonIndex =
target.querySelectorAll('.capell-authoring-button').length; button.type =
'button'; button.className = 'capell-authoring-button'; button.textContent =
region.label; button.style.transform = `translateY(calc(-100% - ${6 +
buttonIndex * 34}px))`; button.addEventListener('click', (event) => {
event.preventDefault(); event.stopPropagation(); openModal(region); });
target.appendChild(button); }); } window.addEventListener('message', (event) =>
{ if (event.origin !== window.location.origin) { return; } if (event.data?.type
=== 'capell-authoring:saved') { const redirectUrl =
event.data?.detail?.redirectUrl || event.data?.detail?.[0]?.redirectUrl;
closeModal(); if (redirectUrl) { window.location.assign(redirectUrl); return; }
window.location.reload(); } }); return { renderRegions }; })();
window.CapellFrontendAuthoring.renderRegions(
@json($regions)
);
