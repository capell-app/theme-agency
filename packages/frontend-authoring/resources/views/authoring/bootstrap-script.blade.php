window.CapellFrontendAuthoring = window.CapellFrontendAuthoring || (function ()
{ let editMode = false; let modalRestoreTarget = null; let modalKeydownHandler =
null; let modalLoadTimer = null; let modalIsDirty = false; let activeMenu =
null; let activeMenuTrigger = null; let bottomOffsetFrame = null; let
originalBodyPaddingBottom = null; const labels = { adminEditing:
@json(__('capell-frontend-authoring::authoring.admin_editing'))
, cached:
@json(__('capell-frontend-authoring::authoring.cached'))
, closeEditor:
@json(__('capell-frontend-authoring::authoring.close_editor'))
, discardChanges:
@json(__('capell-frontend-authoring::authoring.discard_changes'))
, edit:
@json(__('capell-frontend-authoring::authoring.edit'))
, editorLoading:
@json(__('capell-frontend-authoring::authoring.editor_loading'))
, editorLoadError:
@json(__('capell-frontend-authoring::authoring.editor_load_error'))
, editableArea:
@json(__('capell-frontend-authoring::authoring.editable_area'))
, editableAreas:
@json(__('capell-frontend-authoring::authoring.editable_areas'))
, editingVisible:
@json(__('capell-frontend-authoring::authoring.editing_visible'))
, hideEditAreas:
@json(__('capell-frontend-authoring::authoring.hide_edit_areas'))
, notCached:
@json(__('capell-frontend-authoring::authoring.not_cached'))
, publishedStatus:
@json(__('capell-frontend-authoring::authoring.published_status'))
, savedDraftStatus:
@json(__('capell-frontend-authoring::authoring.saved_draft_status'))
, savedPublishedStatus:
@json(__('capell-frontend-authoring::authoring.saved_published_status'))
, showEditAreas:
@json(__('capell-frontend-authoring::authoring.show_edit_areas'))
, updated:
@json(__('capell-frontend-authoring::authoring.updated'))
, updatedBy:
@json(__('capell-frontend-authoring::authoring.updated_by'))
, }; const bannerContext =
@json($banner)
; function editableAreaCountLabel(count) { return count === 1 ?
labels.editableArea : labels.editableAreas.replace(':count', String(count)); }
function escapeHtml(value) { return String(value || '').replace(/[&<>"']/g,
(character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'":
'&#039;', }[character])); } function focusableElements(root) { const selector =
[ 'button', 'iframe', 'a[href]', 'input', 'select', 'textarea',
'[tabindex]:not([tabindex="-1"])', ].join(', '); return
Array.from(root.querySelectorAll(selector)) .filter((element) => typeof
element.focus === 'function' && ! element.hasAttribute('disabled') &&
element.getAttribute('aria-hidden') !== 'true'); } function
appendToolbarText(parent, className, text, title) { if (! text) { return null; }
const element = document.createElement('span'); element.className = className;
element.textContent = String(text); if (title) { element.setAttribute('title',
String(title)); } parent.appendChild(element); return element; } function
showToast(message, type = 'success') {
document.querySelectorAll('.capell-authoring-toast').forEach((toast) =>
toast.remove()); const toast = document.createElement('div'); toast.className =
`capell-authoring-toast capell-authoring-toast--${type}`;
toast.setAttribute('role', 'status'); toast.setAttribute('aria-live', 'polite');
toast.textContent = message; document.body.appendChild(toast);
window.setTimeout(() => toast.remove(), 4500); } function closeModal(options =
{}) { const force = options.force === true; if (modalIsDirty && ! force && !
window.confirm(labels.discardChanges)) { return false; }
document.querySelectorAll('.capell-authoring-modal').forEach((modal) =>
modal.remove()); if (modalKeydownHandler) {
document.removeEventListener('keydown', modalKeydownHandler);
modalKeydownHandler = null; } if (modalLoadTimer) {
window.clearTimeout(modalLoadTimer); modalLoadTimer = null; } modalIsDirty =
false; const restoreTarget = modalRestoreTarget; modalRestoreTarget = null; if
(restoreTarget instanceof HTMLElement && restoreTarget.isConnected) {
restoreTarget.focus({ preventScroll: true }); return true; } const
fallbackTarget = document.querySelector('.capell-authoring-toolbar__toggle'); if
(fallbackTarget instanceof HTMLElement) { fallbackTarget.focus({ preventScroll:
true }); } return true; } function markModalLoaded() { const modal =
document.querySelector('.capell-authoring-modal'); if (! modal) { return; }
const panel = modal.querySelector('.capell-authoring-modal__panel'); const
loading = modal.querySelector('.capell-authoring-modal__loading'); const frame =
modal.querySelector('.capell-authoring-modal__frame');
panel?.removeAttribute('aria-busy'); if (panel) { delete panel.dataset.loading;
} loading?.remove(); if (frame) { frame.hidden = false; } if (modalLoadTimer) {
window.clearTimeout(modalLoadTimer); modalLoadTimer = null; } } function
openModal(region) { closeMenu(); closeModal(); modalRestoreTarget =
document.activeElement instanceof HTMLElement ? document.activeElement : null;
const overlay = document.createElement('div'); overlay.className =
'capell-authoring-modal'; const regionLabel = escapeHtml(region.label); const
editUrl = escapeHtml(region.edit_url); overlay.innerHTML = `
<div class="capell-authoring-modal__backdrop"></div>
<div
    class="capell-authoring-modal__panel"
    role="dialog"
    aria-modal="true"
    aria-label="${regionLabel}"
    aria-busy="true"
    data-loading="true"
>
    <div class="capell-authoring-modal__header">
        <div class="capell-authoring-modal__eyebrow">
            ${escapeHtml(labels.adminEditing)}
        </div>
        <div class="capell-authoring-modal__title">${regionLabel}</div>
    </div>
    <button
        class="capell-authoring-modal__close"
        type="button"
        aria-label="${escapeHtml(labels.closeEditor)}"
    >
        ${escapeHtml(labels.closeEditor)}
    </button>
    <div
        class="capell-authoring-modal__loading"
        role="status"
        aria-live="polite"
    >
        <span class="capell-authoring-modal__spinner" aria-hidden="true"></span>
        <span>${escapeHtml(labels.editorLoading)}</span>
    </div>
    <iframe
        class="capell-authoring-modal__frame"
        src="${editUrl}"
        title="${regionLabel}"
        hidden
    ></iframe>
</div>
`; document.body.appendChild(overlay); const panel =
overlay.querySelector('.capell-authoring-modal__panel'); const closeButton =
overlay.querySelector('.capell-authoring-modal__close'); const frame =
overlay.querySelector('.capell-authoring-modal__frame'); const loading =
overlay.querySelector('.capell-authoring-modal__loading'); modalLoadTimer =
window.setTimeout(() => { if (panel?.dataset.loading !== 'true' || ! loading) {
return; } loading.innerHTML = `
<strong>${escapeHtml(labels.editorLoadError)}</strong>
`; }, 12000); modalKeydownHandler = (event) => { if (event.key === 'Escape') {
event.preventDefault(); closeModal(); return; } if (event.key !== 'Tab' || !
panel) { return; } const focusable = focusableElements(panel); if
(focusable.length === 0) { return; } const first = focusable[0]; const last =
focusable[focusable.length - 1]; if (event.shiftKey && document.activeElement
=== first) { event.preventDefault(); last.focus(); return; } if (!
event.shiftKey && document.activeElement === last) { event.preventDefault();
first.focus(); } }; document.addEventListener('keydown', modalKeydownHandler);
closeButton.addEventListener('click', () => closeModal());
overlay.querySelector('.capell-authoring-modal__backdrop').addEventListener('click',
() => closeModal()); frame.addEventListener('load', () => { try { const
frameDocument = frame.contentDocument; if (! frameDocument) { return; }
frameDocument.addEventListener('keydown', (event) => { if (event.key ===
'Escape') { event.preventDefault(); closeModal(); } }); } catch (error) {} });
closeButton.focus({ preventScroll: true }); } function closeMenu() { if (!
activeMenu) { return; } activeMenu.hidden = true; if (activeMenuTrigger) {
activeMenuTrigger.setAttribute('aria-expanded', 'false'); } activeMenu = null;
activeMenuTrigger = null; } function toggleMenu(menu, button) { const shouldOpen
= activeMenu !== menu || menu.hidden; closeMenu(); menu.hidden = ! shouldOpen;
button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false'); activeMenu
= shouldOpen ? menu : null; activeMenuTrigger = shouldOpen ? button : null; }
function ensureStyles() { if
(document.getElementById('capell-authoring-styles')) { return; } const style =
document.createElement('style'); style.id = 'capell-authoring-styles';
style.textContent = ` .capell-authoring-region { position: relative; }
html.capell-authoring-editing .capell-authoring-region { outline: 2px solid
rgba(37, 99, 235, .72); outline-offset: 4px; } .capell-authoring-overlay-layer {
inset: 0; pointer-events: none; position: fixed; z-index: 2147482999; }
.capell-authoring-control { align-items: flex-end; display: flex;
flex-direction: column; gap: 6px; opacity: 0; pointer-events: none; position:
fixed; transform: translateY(-4px); visibility: hidden; }
html.capell-authoring-editing .capell-authoring-control { opacity: 1;
pointer-events: auto; transform: translateY(0); visibility: visible; }
.capell-authoring-control__button, .capell-authoring-menu__item { align-items:
center; background: #111827; border: 0; border-radius: 999px; color: #fff;
cursor: pointer; display: inline-flex; font: 700 12px/1 ui-sans-serif,
system-ui, sans-serif; gap: 6px; min-height: 32px; padding: 8px 11px;
white-space: nowrap; } .capell-authoring-control__button:focus-visible,
.capell-authoring-menu__item:focus-visible,
.capell-authoring-toolbar__toggle:focus-visible,
.capell-authoring-modal__close:focus-visible { outline: 3px solid rgba(96, 165,
250, .85); outline-offset: 2px; } .capell-authoring-menu { background: #fff;
border: 1px solid rgba(15, 23, 42, .12); border-radius: 8px; box-shadow: 0 18px
48px rgba(15, 23, 42, .18); display: grid; gap: 4px; min-width: 190px; padding:
6px; } .capell-authoring-menu[hidden] { display: none; }
.capell-authoring-menu__item { background: transparent; border-radius: 6px;
color: #111827; justify-content: flex-start; width: 100%; }
.capell-authoring-menu__item:hover { background: #eff6ff; color: #1d4ed8; }
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
inline-flex; font: 800 12px/1 ui-sans-serif, system-ui, sans-serif; gap: 6px;
min-height: 34px; padding: 9px 13px; }
.capell-authoring-toolbar__toggle[aria-pressed="true"] { background: #2563eb;
color: #fff; } .capell-authoring-toast { background: #111827; border: 1px solid
rgba(255, 255, 255, .16); border-radius: 8px; box-shadow: 0 18px 48px rgba(15,
23, 42, .24); color: #fff; font: 700 13px/1.4 ui-sans-serif, system-ui,
sans-serif; max-width: min(420px, calc(100vw - 32px)); padding: 12px 14px;
position: fixed; right: 16px; bottom: calc(var(--capell-authoring-bottom-offset,
0px) + 72px); z-index: 2147483002; } .capell-authoring-toast--success {
background: #14532d; } .capell-authoring-modal { inset: 0; position: fixed;
z-index: 2147483001; } .capell-authoring-modal__backdrop { background: rgba(17,
24, 39, .55); inset: 0; position: absolute; } .capell-authoring-modal__panel {
background: #fff; border-radius: 10px; box-shadow: 0 24px 80px rgba(15, 23, 42,
.3); display: grid; grid-template-rows: auto minmax(0, 1fr); height: min(620px,
calc(100vh - 32px)); left: 50%; max-width: calc(100vw - 32px); overflow: hidden;
position: absolute; top: 50%; transform: translate(-50%, -50%); width:
min(820px, calc(100vw - 32px)); } .capell-authoring-modal__header {
border-bottom: 1px solid #e5e7eb; padding: 16px 92px 14px 18px; }
.capell-authoring-modal__eyebrow { color: #64748b; font: 800 11px/1
ui-sans-serif, system-ui, sans-serif; letter-spacing: .04em; margin-bottom: 6px;
text-transform: uppercase; } .capell-authoring-modal__title { color: #111827;
font: 800 16px/1.2 ui-sans-serif, system-ui, sans-serif; }
.capell-authoring-modal__close { align-items: center; background: #111827;
border: 0; border-radius: 999px; color: #fff; cursor: pointer; display: flex;
font: 800 12px/1 ui-sans-serif, system-ui, sans-serif; height: 34px;
justify-content: center; padding: 0 12px; position: absolute; right: 14px; top:
14px; z-index: 2; } .capell-authoring-modal__loading { align-items: center;
color: #334155; display: flex; font: 800 14px/1.4 ui-sans-serif, system-ui,
sans-serif; gap: 12px; justify-content: center; min-height: 320px; padding:
24px; text-align: center; } .capell-authoring-modal__spinner { animation:
capellAuthoringSpin .8s linear infinite; border: 3px solid #dbeafe;
border-top-color: #2563eb; border-radius: 999px; display: inline-block; height:
28px; width: 28px; } @keyframes capellAuthoringSpin { to { transform:
rotate(360deg); } } .capell-authoring-modal__frame { border: 0; height: 100%;
width: 100%; } @media (max-width: 640px) { .capell-authoring-toolbar {
align-items: stretch; grid-template-columns: 1fr; }
.capell-authoring-toolbar__actions { justify-content: stretch; }
.capell-authoring-toolbar__toggle { justify-content: center; width: 100%; }
.capell-authoring-control { right: 0; top: 0; } .capell-authoring-modal__panel {
border-radius: 0; height: 100%; max-width: 100vw; width: 100vw; } } `;
document.head.appendChild(style); } function ensureToolbar(regionCount) { if
(document.getElementById('capell-authoring-toolbar')) { return; } const toolbar
= document.createElement('div'); toolbar.id = 'capell-authoring-toolbar';
toolbar.className = 'capell-authoring-toolbar'; toolbar.setAttribute('role',
'region'); toolbar.setAttribute('aria-label', labels.adminEditing); const
updatedMeta = bannerContext.updated ? `${labels.updated}
${bannerContext.updated}` : ''; const updatedByMeta = bannerContext.updated_by ?
`${labels.updatedBy} ${bannerContext.updated_by}` : ''; const cacheStatus =
bannerContext.html_cached ? labels.cached : labels.notCached; const content =
document.createElement('div'); content.className =
'capell-authoring-toolbar__content'; appendToolbarText(content,
'capell-authoring-toolbar__label', labels.adminEditing);
appendToolbarText(content, 'capell-authoring-toolbar__page', bannerContext.page,
bannerContext.page); appendToolbarText(content,
'capell-authoring-toolbar__meta', editableAreaCountLabel(regionCount));
appendToolbarText(content, 'capell-authoring-toolbar__meta', updatedMeta);
appendToolbarText(content, 'capell-authoring-toolbar__meta', updatedByMeta);
const status = appendToolbarText(content, 'capell-authoring-toolbar__status',
cacheStatus); status.setAttribute('aria-live', 'polite'); const actions =
document.createElement('div'); actions.className =
'capell-authoring-toolbar__actions'; const toggle =
document.createElement('button'); toggle.className =
'capell-authoring-toolbar__toggle'; toggle.type = 'button';
toggle.setAttribute('aria-pressed', 'false'); toggle.setAttribute('aria-label',
labels.showEditAreas); toggle.textContent = labels.showEditAreas;
actions.appendChild(toggle); toolbar.appendChild(content);
toolbar.appendChild(actions); toggle.addEventListener('click', () => { editMode
= ! editMode;
document.documentElement.classList.toggle('capell-authoring-editing', editMode);
toggle.setAttribute('aria-pressed', editMode ? 'true' : 'false');
toggle.setAttribute('aria-label', editMode ? labels.hideEditAreas :
labels.showEditAreas); toggle.textContent = editMode ? labels.editingVisible :
labels.showEditAreas; closeMenu(); setControlsEnabled(editMode);
scheduleControlPositionUpdate(); }); document.body.appendChild(toolbar);
updateBottomOffset(); window.addEventListener('resize',
scheduleBottomOffsetUpdate, { passive: true });
window.addEventListener('resize', scheduleControlPositionUpdate, { passive: true
}); window.addEventListener('scroll', scheduleControlPositionUpdate, { passive:
true }); } function scheduleBottomOffsetUpdate() { if (bottomOffsetFrame !==
null) { return; } bottomOffsetFrame = window.requestAnimationFrame(() => {
bottomOffsetFrame = null; updateBottomOffset(); }); } function
updateBottomOffset() { const debugBars = ['#phpdebugbar', '.phpdebugbar',
'[id^="debugbar"]'] .map((selector) => document.querySelector(selector))
.filter(Boolean); const debugBarHeight = debugBars.reduce((height, element) => {
const rect = element.getBoundingClientRect(); const style =
window.getComputedStyle(element); const isBottomBar = style.position === 'fixed'
&& rect.bottom >= window.innerHeight - 4 && rect.height > 0; return isBottomBar
? Math.max(height, rect.height) : height; }, 0);
document.documentElement.style.setProperty('--capell-authoring-bottom-offset',
`${debugBarHeight}px`); const toolbar =
document.getElementById('capell-authoring-toolbar'); if (toolbar instanceof
HTMLElement) { if (originalBodyPaddingBottom === null) {
originalBodyPaddingBottom =
Number.parseFloat(window.getComputedStyle(document.body).paddingBottom || '0')
|| 0; } document.body.style.paddingBottom = `${originalBodyPaddingBottom +
toolbar.getBoundingClientRect().height + debugBarHeight}px`; } } function
overlayLayer() { let layer =
document.getElementById('capell-authoring-overlay-layer'); if (! layer) { layer
= document.createElement('div'); layer.id = 'capell-authoring-overlay-layer';
layer.className = 'capell-authoring-overlay-layer';
document.body.appendChild(layer); } return layer; } function
setControlsEnabled(enabled) {
document.querySelectorAll('.capell-authoring-control').forEach((control) => {
control.setAttribute('aria-hidden', enabled ? 'false' : 'true');
control.querySelectorAll('button').forEach((button) => { button.tabIndex =
enabled ? 0 : -1; }); }); } function positionControl(control) { const target =
control.capellAuthoringTarget; if (! target || ! target.isConnected) {
control.hidden = true; return; } const rect = target.getBoundingClientRect(); if
(rect.width <= 0 || rect.height <= 0 || rect.bottom < 0 || rect.top >
window.innerHeight) { control.hidden = true; return; } control.hidden = false;
const controlWidth = Math.max(control.offsetWidth, control.scrollWidth, 180);
const controlHeight = control.offsetHeight || 34; const stackOffset =
Number(control.capellAuthoringIndex || 0) * 38; const left = Math.max(16,
Math.min(window.innerWidth - controlWidth - 16, rect.right - controlWidth));
const preferredTop = rect.top - controlHeight - 8; const baseTop = preferredTop
> 8 ? preferredTop : rect.top + 8; const top = Math.min(window.innerHeight -
controlHeight - 16, baseTop + stackOffset); control.style.left = `${left}px`;
control.style.top = `${top}px`; } function updateControlPositions() {
document.querySelectorAll('.capell-authoring-control').forEach((control) => {
positionControl(control); }); } function scheduleControlPositionUpdate() {
window.requestAnimationFrame(updateControlPositions); } function clearRegions()
{ closeMenu();
document.querySelectorAll('.capell-authoring-control').forEach((control) =>
control.remove());
document.getElementById('capell-authoring-overlay-layer')?.remove();
document.querySelectorAll('.capell-authoring-region').forEach((element) => {
element.classList.remove('capell-authoring-region'); delete
element.dataset.capellAuthoringSelector; }); } function
createRegionControl(regions) { const control = document.createElement('div');
control.className = 'capell-authoring-control';
control.setAttribute('aria-hidden', 'true'); if (regions.length === 1) { const
region = regions[0]; const button = document.createElement('button');
button.type = 'button'; button.className = 'capell-authoring-control__button';
button.tabIndex = -1; button.textContent = `${labels.edit} ${region.label}`;
button.setAttribute('aria-label', `${labels.edit} ${region.label}`);
button.addEventListener('click', (event) => { event.preventDefault();
event.stopPropagation(); openModal(region); }); control.appendChild(button);
return control; } const button = document.createElement('button'); button.type =
'button'; button.className = 'capell-authoring-control__button';
button.textContent = `${labels.edit} (${regions.length})`; button.tabIndex = -1;
button.setAttribute('aria-expanded', 'false'); const menu =
document.createElement('div'); menu.className = 'capell-authoring-menu';
menu.hidden = true; regions.forEach((region) => { const item =
document.createElement('button'); item.type = 'button'; item.className =
'capell-authoring-menu__item'; item.tabIndex = -1; item.textContent =
region.label; item.addEventListener('click', (event) => {
event.preventDefault(); event.stopPropagation(); openModal(region); });
menu.appendChild(item); }); button.addEventListener('click', (event) => {
event.preventDefault(); event.stopPropagation(); toggleMenu(menu, button); });
control.appendChild(button); control.appendChild(menu); return control; }
function renderRegions(regions) { clearRegions(); if (! regions || typeof
regions !== 'object') { return; } const editableRegions =
Object.values(regions); ensureStyles(); ensureToolbar(editableRegions.length);
const groups = new Map(); editableRegions.forEach((region) => { const target =
document.querySelector(region.selector); if (! target) { return; } const key =
region.target || region.selector; const group = groups.get(key) || { target,
regions: [] }; group.regions.push(region); groups.set(key, group); });
Array.from(groups.entries()).forEach(([selector, group], index) => {
group.target.dataset.capellAuthoringSelector = selector;
group.target.classList.add('capell-authoring-region'); const control =
createRegionControl(group.regions); control.capellAuthoringTarget =
group.target; control.capellAuthoringIndex = index;
overlayLayer().appendChild(control); }); setControlsEnabled(editMode);
scheduleControlPositionUpdate(); } window.addEventListener('click', (event) => {
if (activeMenu && ! event.target.closest('.capell-authoring-control')) {
closeMenu(); } }); window.addEventListener('keydown', (event) => { if (event.key
=== 'Escape' && activeMenu) { event.preventDefault(); const trigger =
activeMenuTrigger; closeMenu(); trigger?.focus({ preventScroll: true }); } });
window.addEventListener('message', (event) => { if (event.origin !==
window.location.origin) { return; } if (event.data?.type ===
'capell-authoring:editor-loaded') { markModalLoaded(); return; } if
(event.data?.type === 'capell-authoring:dirty') { modalIsDirty = true; return; }
if (event.data?.type === 'capell-authoring:saved') { modalIsDirty = false; const
detail = event.data?.detail || {}; const firstDetail = Array.isArray(detail) ?
detail[0] : detail; const redirectUrl = firstDetail?.redirectUrl; const status =
firstDetail?.status; const message = status === 'pending_approval' ?
labels.savedDraftStatus : labels.savedPublishedStatus; closeModal({ force: true
}); showToast(message); window.setTimeout(() => { if (redirectUrl) {
window.location.assign(redirectUrl); return; } window.location.reload(); },
1200); } }); return { renderRegions }; })();
window.CapellFrontendAuthoring.renderRegions(
@json($regions)
);
