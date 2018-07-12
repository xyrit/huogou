<?php
header("Content-type: text/html; charset=utf-8");
/**
 *���ܣ������ļ�
 *�汾��1.0
 *�޸����ڣ�2014-06-26
 '˵����
 '���´���ֻ��Ϊ�˷����̻����Զ��ṩ���������룬�̻����Ը����Լ�����Ҫ�����ռ����ĵ���д,����һ��Ҫʹ�øô��롣
 '�ô������ѧϰ���о������ƼƷѽӿ�ʹ�ã�ֻ���ṩһ���ο���
 */
//
global $orderUrl,$queryResultUrl,$h5url,$pcurl,$appid,$cpvkey,$platpkey;

//�����̻���̨����url
$iapppayCpUrl="http://ipay.iapppay.com:9999";
//��¼������֤�ӿ� url
$tokenCheckUrl=$iapppayCpUrl . "/openid/openidcheck";

//�µ��ӿ� url
$orderUrl=$iapppayCpUrl . "/payapi/order";

//֧�������ѯ�ӿ� url
$queryResultUrl=$iapppayCpUrl ."/payapi/queryresult";

//��Լ��ѯ�ӿ�url
$querysubsUrl=$iapppayCpUrl."/payapi/subsquery";

//��Լ��Ȩ�ӿ�Url
$ContractAuthenticationUrl=$iapppayCpUrl."/payapi/subsauth";

//ȡ����Լ�ӿ�Url
$subcancel=$iapppayCpUrl."/payapi/subcancel";
//H5��ת��֧���ӿ�Url
$h5url="https://web.iapppay.com/h5/exbegpay";
//pc url
$pcurl="https://web.iapppay.com/pc/exbegpay";


$appid = "3003377542";
//cp˽Կ
$cpvkey="MIICWwIBAAKBgQCKDZ8f/3yYfXIxlVi65cPAefk78OPDBgWexUo8/eSkKcWI4X5tsnqMWtcZ9cQzpGW3FjIzHxM7NlhmgMgudX1KwSR/jwRZUBW9+P+rd4jE2LuXskJvyVT2u/o58Uh6Zzb9Wo5UKWfIEZe5aXqdmXTSSkgmttwlaxoihIqEfu9P8QIDAQABAoGAJilFx06UXoKuwk4KTP+ecOJGpu8bxpkvjIf00Y9NWKPDWucaT7B6d7nUo/Rv2+ahx053afI5GrEXFp6at1z62Pr11TbRySahTnw1SbemKqQizubC2ZBs53NC1p80GlqzbTI2Int4pB2TgoHvYboaFxtsGtlfE5RWNr6kL2OJM8ECQQDK9NRDJx3gtmFCrS2a+4iYAJH7J/wUK8DX9xO38QUj23fLsX2DzgAHsG4JNdNjzAjH3MCBLkKc+LAYethZKrj9AkEAriJTrIkRQwXBP4K8iLoyVBkXx+xESzLvO4leYW+D+fagcM0O7DZcRXxZQrx1EAQ6FcpUGdeH+nolpkv0nbRvBQJAXatnd+LK2FZ0Rxi0Tq4+maDRvy/yGMEkzMf88s0rSSRWgs1VF4rw2puj/V45RPr7JnsM4dIe7mGcrH+t8GFWZQJAGTnT0UzR+VmeEytHUK9YlyJDdazef95TFdbim07iWZXGzFCIduOxHkfTTn2qn7VdDMcQw+WbR0fmqF6cgzQeWQJAAbT11KUxG+nJ4C4vgTByirTrWbJgAtPQj0RxnX9aRFcAuRzxJ5rtkRU0BbqohtQRJqR0UvZXN5EdPVYdfG4Iuw==";
//ƽ̨��Կ
$platpkey="MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCWDXaGN2sjXl6VouBHyP/hVcyuKcwIj98IElPbpIv8ueVYNcAKLtWzqEnrojL/Xsym7UuAdlpmmGr4bXmKSdrw5fZzQ6HcKm9rOl75L6bbtzjYTOD9G6AAKUcdrZn6w5NDQOhYvusiUb7LBDkOxa7kgGmLGSCZ4tEKBWlQufgegwIDAQAB";

?>