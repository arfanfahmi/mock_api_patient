<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\LogCuti;
use App\Models\Pegawai;
use App\Models\Disposisi;
use App\Models\Realisasi;
use App\Mail\PersetujuanCutiMail;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait WhatsAppTrait
{
  public function approvalViaWhatsAppForUser($wamid, $tipeRespon, $keterangan) {
    $disposisi = Disposisi::with('relasiDisposisi', 'realisasi')
      ->where('wamid', $wamid)
      ->first();

    $penerimaRevisi = '';
    if ($disposisi->relasiDisposisi->penerima == 'direksi') {
      $penerimaRevisi = 'keuangan';
    } else if ($disposisi->relasiDisposisi->penerima == 'manager') {
      $penerimaRevisi = 'unit';
    }

    if (!in_array($disposisi->status, ['diajukan', 'direvisi'])) {
      return [
        'status' => 'fail', 
        'data_disposisi' => $disposisi 
      ];
    }
    
    if ($disposisi->status == 'direvisi') {
      return [
        'status' => 'fail_under_revision',
        'penerima_revisi' => $penerimaRevisi,
        'data_disposisi' => $disposisi 
      ];
    }

    $pengajuanRealisasi = getRealisasi($disposisi->realisasi->uuid);

    $response = Http::post(route('api.approval.approve', ['whatsAppApproval' => 1]), [
      'realisasi_id' => $disposisi->realisasi_id,
      'total_nilai_diajukan' => $pengajuanRealisasi->total_pengajuan_keu ?? ($pengajuanRealisasi->total_pengajuan_tim ?? $pengajuanRealisasi->total_pengajuan),
      'last_disposition_id' => $disposisi->id,
      'tipe_respon' => strtolower($tipeRespon), 
      'catatan' => $keterangan, 
      'catatan_revisi' => $keterangan,
      'penerima_revisi' => $penerimaRevisi
    ]);
    Log::info($response->body());

    // Handle the response as needed
    $response =  $response->body();

    //Mail::to($pegawai->email)->send(new PersetujuanCutiMail($pegawai, $cutiAfterApproval));

    return [
      'status' => 'success', 
      'penerima_revisi' => $penerimaRevisi,
      'data_disposisi' => $disposisi
    ];
  }

  
  /* Kirim Notifikasi jika ada approval baru */
  public function sendApprovalNotificationForUser($realisasiId, $noHpPenerima, $nilaiPengajuan, $underRevision = null) {
    $realisasi = Realisasi::with('proker', 'proker.unit', 'proker.sasaran.tahun')
      ->where('id', $realisasiId)
      ->first();

    //no pengajuan
    $explodedNoRealisasi = explode('/', $realisasi->no_realisasi);
    $noRealisasi = $explodedNoRealisasi[2];

    //no HP atasan
    if (substr($noHpPenerima, 0, 1) == '0') {
      $noHpPenerima = preg_replace("/[^0-9+]/", "", $noHpPenerima);
      $noHpPenerima = '62'.ltrim($noHpPenerima, '0');
    }

    try {
      $client = new Client();

      $response = $client->post('https://graph.facebook.com/v18.0/318850654638191/messages', [
          'headers' => [
              'Authorization' => 'Bearer EAAERlMxAOrkBO3ofIYhfRWoJNCPSMEY0C0zx8nHXPiewozaKPeRV9sFo1Pv43z18Lmfcgtr2WlUp4uXlCL0DvIKSfEkOIEtSZBDOHp1MbtYLD1ZBZB81k0frrg3XwQE6ksWIuqutMv3ZBWYJasCXdvlTKERKKAMVv6rk6LXV7Y1Or7gQZC1ucinlxmNQcs1rL',
              'Content-Type' => 'application/json',
          ],
          'json' => [
              'messaging_product' => 'whatsapp',
              'to' => $noHpPenerima,
              'type' => 'template', 
              'template' => [
                  'name' => 'notif_approval',
                  'language' => [
                      'code' => 'id'
                  ],
                  'components' => [
                      [
                        "type" => "header",
                        "parameters"=> [
                          [
                              "type" => "document",
                              "document" => [
                                "link" => asset('storage/uploads/surat_pengajuan/'.$realisasi->uuid.'.pdf')
                                //"link" => "https://sipres.rspkuwonosobo.id/storage/uploads/images/cuti/cc33581828ff9fa87bf08fe8015c7b4c09b1546b7bd239db88480c2b5f972645_20240502_090440.jpg"
                              ]
                          ]
                        ]
                      ],

                      [
                          'type' => 'body',
                          'parameters' => [
                              ['type' => 'text', 'text' => $noRealisasi],
                              ['type' => 'text', 'text' => $realisasi->proker->unit->nama_unit],
                              ['type' => 'text', 'text' => 'Rp '.number_format($nilaiPengajuan, 0, ',', '.')],
                          ]
                      ],
                      
                      [
                          "type" => "button",
                          "sub_type" => "flow",
                          "index" => "0",
                          "parameters" => [
                              [
                                  "type" => "action",
                                  "action" => [
                                      "flow_token" => "unused",
                                      "flow_action_data" => [
                                          "version" => "3.1",
                                          "screens" => [
                                            [
                                              "id" => "RECOMMEND",
                                              "title" => "Approval Anggaran",
                                              "data" => [],
                                              "terminal" => true,
                                              "layout" => [
                                                  "type" => "SingleColumnLayout",
                                                  "children" => [
                                                      [
                                                          "type" => "Form",
                                                          "name" => "flow_path",
                                                          "children" => [
                                                              [
                                                                  "type" => "TextSubheading",
                                                                  "text" => "Setujui Pengajuan?"
                                                              ],
                                                              [
                                                                  "type" => "RadioButtonsGroup",
                                                                  "label" => "Approval Anda:",
                                                                  "name" => "recommend_radio",
                                                                  "data-source" => [
                                                                      [
                                                                          "id" => "0_Setujui",
                                                                          "title" => "Setujui"
                                                                      ],
                                                                      [
                                                                          "id" => "1_Tolak_Pengajuan",
                                                                          "title" => "Tolak Pengajuan"
                                                                      ],
                                                                      [
                                                                          "id" => "2_Minta_Revisi",
                                                                          "title" => "Minta Revisi"
                                                                      ]
                                                                  ],
                                                                  "required" => true
                                                              ],
                                                              [
                                                                  "type" => "TextArea",
                                                                  "label" => "Catatan",
                                                                  "required" => true,
                                                                  "name" => "comment_text",
                                                                  "helper-text" => "Ketik \"-\" jika tidak ada catatan"
                                                              ],
                                                              [
                                                                  "type" => "Footer",
                                                                  "label" => "Kirim Approval",
                                                                  "on-click-action" => [
                                                                      "name" => "complete",
                                                                      "payload" => [
                                                                          "screen_0_recommend_0" => '${form.recommend_radio}',
                                                                          "screen_0_comment_1" => '${form.comment_text}'
                                                                      ]
                                                                  ]
                                                              ]
                                                          ]
                                                      ]
                                                  ]
                                              ]
                                            ]
                                          ]
                                      ]
                                  ]
                              ]
                          ]
                      ]
                  ]
              ] 
          ]
      ]);

      // Get the response body as a string
      $body = $response->getBody()->getContents();
  
      // Do something with the response
      return $body;
    } catch(ClientException $e) {
      Log::error('ClientException: ' . $e->getMessage(), ['exception' => $e]);
        return false;
    } catch(\Exception $e) {
      Log::error('ClientException: ' . $e->getMessage(), ['exception' => $e]);
        return false;
    }
  }

  /* 
   * Kirim Notifikasi jika ada approval baru 
   */
  public function sendRevisionNotificationForUser($realisasiId, $noHpPenerima, $nilaiPengajuan, $underRevision = null) {
    $realisasi = Realisasi::with('proker', 'proker.unit', 'proker.sasaran.tahun')
      ->where('id', $realisasiId)
      ->first();

    //no pengajuan
    $explodedNoRealisasi = explode('/', $realisasi->no_realisasi);
    $noRealisasi = $explodedNoRealisasi[2];

    //no HP atasan
    if (substr($noHpPenerima, 0, 1) == '0') {
      $noHpPenerima = preg_replace("/[^0-9+]/", "", $noHpPenerima);
      $noHpPenerima = '62'.ltrim($noHpPenerima, '0');
    }

    try {
      $client = new Client();

      $response = $client->post('https://graph.facebook.com/v18.0/318850654638191/messages', [
          'headers' => [
              'Authorization' => 'Bearer EAAERlMxAOrkBO3ofIYhfRWoJNCPSMEY0C0zx8nHXPiewozaKPeRV9sFo1Pv43z18Lmfcgtr2WlUp4uXlCL0DvIKSfEkOIEtSZBDOHp1MbtYLD1ZBZB81k0frrg3XwQE6ksWIuqutMv3ZBWYJasCXdvlTKERKKAMVv6rk6LXV7Y1Or7gQZC1ucinlxmNQcs1rL',
              'Content-Type' => 'application/json',
          ],
          'json' => [
              'messaging_product' => 'whatsapp',
              'to' => $noHpPenerima,
              'type' => 'template', 
              'template' => [
                  'name' => 'notif_revisi',
                  'language' => [
                      'code' => 'id'
                  ],
                  'components' => [
                      [
                        "type" => "header",
                        "parameters"=> [
                          [
                              "type" => "document",
                              "document" => [
                                "link" => asset('storage/uploads/surat_pengajuan/revisi_'.$realisasi->uuid.'.pdf')
                                //"link" => "https://sipres.rspkuwonosobo.id/storage/uploads/images/cuti/cc33581828ff9fa87bf08fe8015c7b4c09b1546b7bd239db88480c2b5f972645_20240502_090440.jpg"
                              ]
                          ]
                        ]
                      ],

                      [
                          'type' => 'body',
                          'parameters' => [
                              ['type' => 'text', 'text' => $noRealisasi],
                              ['type' => 'text', 'text' => $realisasi->proker->unit->nama_unit],
                              ['type' => 'text', 'text' => 'Rp '.number_format($nilaiPengajuan, 0, ',', '.')],
                          ]
                      ],
                      
                      [
                          "type" => "button",
                          "sub_type" => "flow",
                          "index" => "0",
                          "parameters" => [
                              [
                                  "type" => "action",
                                  "action" => [
                                      "flow_token" => "unused",
                                      "flow_action_data" => [
                                          "version" => "3.1",
                                          "screens" => [
                                            [
                                              "id" => "RECOMMEND",
                                              "title" => "Approval Revisi",
                                              "data" => [],
                                              "terminal" => true,
                                              "layout" => [
                                                  "type" => "SingleColumnLayout",
                                                  "children" => [
                                                      [
                                                          "type" => "Form",
                                                          "name" => "flow_path",
                                                          "children" => [
                                                              [
                                                                  "type" => "TextSubheading",
                                                                  "text" => "Setujui Revisi?"
                                                              ],
                                                              [
                                                                  "type" => "RadioButtonsGroup",
                                                                  "label" => "Approval Anda",
                                                                  "name" => "recommend_radio",
                                                                  "data-source" => [
                                                                      [
                                                                          "id" => "0_Setujui",
                                                                          "title" => "Setujui"
                                                                      ],
                                                                      [
                                                                          "id" => "1_Tolak_Pengajuan",
                                                                          "title" => "Tolak Pengajuan"
                                                                      ],
                                                                      [
                                                                          "id" => "2_Minta_Revisi_Lagi",
                                                                          "title" => "Minta Revisi Lagi"
                                                                      ]
                                                                  ],
                                                                  "required" => true
                                                              ],
                                                              [
                                                                  "type" => "TextArea",
                                                                  "label" => "Catatan",
                                                                  "required" => true,
                                                                  "name" => "comment_text",
                                                                  "helper-text" => "Ketik \"-\" jika tidak ada catatan"
                                                              ],
                                                              [
                                                                  "type" => "Footer",
                                                                  "label" => "Kirim Approval Revisi",
                                                                  "on-click-action" => [
                                                                      "name" => "complete",
                                                                      "payload" => [
                                                                          "screen_0_recommend_0" => '${form.recommend_radio}',
                                                                          "screen_0_comment_1" => '${form.comment_text}'
                                                                      ]
                                                                  ]
                                                              ]
                                                          ]
                                                      ]
                                                  ]
                                              ]
                                            ]
                                          ]
                                      ]
                                  ]
                              ]
                          ]
                      ]
                  ]
              ] 
          ]
      ]);

      // Get the response body as a string
      $body = $response->getBody()->getContents();
  
      // Do something with the response
      return $body;
    } catch(ClientException $e) {
      Log::error('ClientException: ' . $e->getMessage(), ['exception' => $e]);
        return false;
    } catch(\Exception $e) {
      Log::error('ClientException: ' . $e->getMessage(), ['exception' => $e]);
        return false;
    }
  }
}