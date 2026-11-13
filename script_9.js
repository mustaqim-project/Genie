
    function applyCustomsDocPreset(presetVal) {
        if (!presetVal) return;
        const parts = presetVal.split('|');
        if (parts[0]) document.getElementById('add_doc_kode').value = parts[0];
        if (parts[1]) document.getElementById('add_doc_nama').value = parts[1];
        if (parts[2]) document.getElementById('add_doc_instansi').value = parts[2];
    }

    function openFulfillDocModal(doc, itemName, hsCode) {
        if (!doc) return;
        const modal = document.getElementById('modal-fulfill-item-doc');
        const form = document.getElementById('form-fulfill-item-doc');
        
        form.action = `/core/document/01a1d0c2-2d49-7125-83ff-5e0e28e5ef6f/item-document/${doc.id}`;
        
        document.getElementById('fulfill_doc_code_badge').textContent = doc.kode_dokumen || '-';
        document.getElementById('fulfill_doc_name').textContent = doc.nama_dokumen || 'Dokumen Tambahan';
        document.getElementById('fulfill_doc_instansi').textContent = doc.instansi_penerbit || 'Instansi Terkait';
        document.getElementById('fulfill_item_hs').textContent = hsCode || '-';
        document.getElementById('fulfill_item_name').textContent = itemName || '-';

        document.getElementById('fulfill_nomor_dokumen').value = doc.nomor_dokumen || '';
        document.getElementById('fulfill_tanggal_dokumen').value = doc.tanggal_dokumen ? doc.tanggal_dokumen.substring(0, 10) : '';
        document.getElementById('fulfill_catatan').value = doc.catatan || '';

        const statusSelect = document.getElementById('fulfill_status');
        if (statusSelect) {
            statusSelect.value = doc.status || 'UPLOADED';
        }

        const curFile = document.getElementById('fulfill_current_file');
        if (curFile) {
            if (doc.file_name) {
                curFile.textContent = `Berkas tersimpan: ${doc.file_name}`;
                curFile.classList.remove('hidden');
            } else {
                curFile.classList.add('hidden');
            }
        }

        modal.classList.remove('hidden');
        if (window.lucide) { lucide.createIcons(); }
    }

    function openEditItemModal(item) {
        if (!item) return;
        const modal = document.getElementById('modal-edit-item');
        const form = document.getElementById('form-edit-item');

        form.action = `/core/document/01a1d0c2-2d49-7125-83ff-5e0e28e5ef6f/items/${item.id}`;

        document.getElementById('edit_item_kode_hs').value = item.kode_hs || '';
        document.getElementById('edit_item_uraian_barang').value = item.uraian_barang || '';
        document.getElementById('edit_item_hs_legal_basis').value = item.hs_legal_basis || '';
        document.getElementById('edit_item_negara_asal').value = item.negara_asal || 'CN';
        document.getElementById('edit_item_jumlah_kemasan').value = item.jumlah_kemasan || 1;
        document.getElementById('edit_item_jenis_kemasan').value = item.jenis_kemasan || 'CTN';
        document.getElementById('edit_item_berat_bruto').value = item.berat_bruto || 0;
        document.getElementById('edit_item_berat_netto').value = item.berat_netto || 0;
        document.getElementById('edit_item_nilai_fob').value = item.nilai_fob || 0;
        document.getElementById('edit_item_tarif_bm_persen').value = item.tarif_bm_persen ?? 5.0;
        document.getElementById('edit_item_tarif_ppn_persen').value = item.tarif_ppn_persen ?? 11.0;
        document.getElementById('edit_item_tarif_pph_persen').value = item.tarif_pph_persen ?? 2.5;

        lookupEditHsModal(item.kode_hs);
        modal.classList.remove('hidden');
        if (window.lucide) { lucide.createIcons(); }
    }

    async function lookupEditHsModal(hsCode) {
        if (!hsCode || hsCode.length < 4) return;
        const badge = document.getElementById('edit_hs_lartas_badge');
        try {
            const res = await fetch(`/ceisa/tarif-hs/${encodeURIComponent(hsCode)}`);
            const data = await res.json();
            if (data && data.success) {
                if (badge) {
                    badge.classList.remove('hidden');
                    if (data.has_lartas) {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 text-amber-800 dark:text-amber-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>✓ <strong>Bebas Lartas:</strong> Pos Tarif ${data.code} siap diajukan.</span>`;
                    }
                }
            }
        } catch (e) {
            console.error('Gagal lookup HS edit:', e);
        }
    }

    async function lookupHsModal(hsCode) {
        if (!hsCode || hsCode.length < 4) return;
        const badge = document.getElementById('modal_hs_lartas_badge');
        try {
            const res = await fetch(`/ceisa/tarif-hs/${encodeURIComponent(hsCode)}`);
            const data = await res.json();
            if (data && data.success) {
                const uraianInput = document.querySelector('#modal-add-item input[name="uraian_barang"]');
                const legalInput = document.querySelector('#modal-add-item input[name="hs_legal_basis"]');
                const bmInput = document.querySelector('#modal-add-item input[name="tarif_bm_persen"]');
                const ppnInput = document.querySelector('#modal-add-item input[name="tarif_ppn_persen"]');
                const pphInput = document.querySelector('#modal-add-item input[name="tarif_pph_persen"]');

                if (uraianInput && (!uraianInput.value || uraianInput.value.includes('INDUSTRIAL SEWING MACHINE'))) {
                    uraianInput.value = data.desc || '';
                }
                if (legalInput) {
                    legalInput.value = data.legal || '';
                }
                if (bmInput && data.bm !== undefined) {
                    bmInput.value = data.bm;
                }
                if (ppnInput && data.ppn !== undefined) {
                    ppnInput.value = data.ppn;
                }
                if (pphInput && data.pph !== undefined) {
                    pphInput.value = data.pph;
                }

                if (badge) {
                    badge.classList.remove('hidden');
                    if (data.has_lartas) {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 text-amber-800 dark:text-amber-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>✓ <strong>Bebas Lartas:</strong> Pos Tarif ${data.code} siap diajukan.</span>`;
                    }
                }
            }
        } catch (e) {
            console.error('Gagal lookup HS:', e);
        }
    }

    function openPortalModalShow() {
        document.getElementById('modal-portal-share-show').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closePortalModalShow() {
        document.getElementById('modal-portal-share-show').classList.add('hidden');
    }

    function copyPortalUrlShow() {
        var input = document.getElementById('portal-url-input-show');
        if (!input.value) return;
        navigator.clipboard.writeText(input.value).then(function() {
            var btnText = document.getElementById('copy-btn-text-show');
            btnText.innerText = 'Tersalin!';
            setTimeout(function() { btnText.innerText = 'Salin'; }, 2000);
        });
    }
