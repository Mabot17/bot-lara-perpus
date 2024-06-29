<div class="modal fade bd-example-modal-lg" role="dialog" aria-modal="true" id="detail-penjualan-modal">
    <div class="modal-dialog  modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-block text-center pb-3 border-bttom">
                <h3 class="modal-title" id="exampleModalCenterTitle02">
                    Detail Data Mahasiswa
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </h3>

            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group" class="custom-file-small">
                            <div class="crm-profile-img-edit position-relative text-center">
                                <img class="mhs-detail-profile-pic rounded avatar-130 border" id="" src="{{ asset('/images/user/default-profile.jpg') }}" alt="profile-pic" id="txtFoto">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group ">
                                    <label for="txtNama" class="h5">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="txtNama" name="mhs_nama" readonly>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group ">
                                    <label for="txtProdi" class="h5">Prodi</label>
                                    <input type="text" class="form-control" id="txtProdi" name="mhs_nbi" readonly>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="txtNbi" class="h5">NBI</label>
                                    <input type="text" class="form-control" id="txtNbi" name="mhs_nbi" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group ">
                            <label for="txtTglLahir" class="h5">Tanggal Lahir</label>
                            <input type="text" class="form-control" id="txtTglLahir" name="mhs_tgl_lahir" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group ">
                            <label for="txtKelamin" class="h5">Jenis Kelamin</label>
                            <input type="text" class="form-control" id="txtKelamin" name="mhs_jenis_kelamin" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group ">
                            <label for="txtHp" class="h5">No. HP</label>
                            <input type="number" class="form-control" id="txtHp" name="mhs_no_hp" readonly>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group ">
                            <label for="txtAlamat">Alamat</label>
                            <textarea class="form-control" rows="3" id="txtAlamat" name="mhs_alamat" readonly></textarea>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group ">
                            <label for="txtPembimbing" class="h5">Dosen Pembimbing</label>
                            <input type="text" class="form-control" id="txtPembimbing" name="mhs_jenis_kelamin" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
