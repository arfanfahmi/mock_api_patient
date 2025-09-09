<?php

namespace App\Policies;

use App\Models\Pegawai;
use App\Models\Tahun;
use Illuminate\Auth\Access\Response;

class TahunPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Pegawai $pegawai): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Pegawai $pegawai, Tahun $tahun): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Pegawai $pegawai): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Pegawai $pegawai, Tahun $tahun): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Pegawai $pegawai, Tahun $tahun): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Pegawai $pegawai, Tahun $tahun): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Pegawai $pegawai, Tahun $tahun): bool
    {
        //
    }
}
