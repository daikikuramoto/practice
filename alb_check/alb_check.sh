#/bin/bash

alb_show_health() {
  local profile=$1;

  #ALBのARNを取得
  local alb_arn=`aws elbv2 describe-load-balancers \
    --profile $profile \
    --query 'LoadBalancers[].[LoadBalancerArn]' \
    --output text` || return $?

  #取得したARN1つずつに対して下記ループを実行
  for alb_arns in ${alb_arn[@]}
  do

    #ALB ARNからTarget Groupを取得
    local target_groups=`aws elbv2 describe-target-groups\
      --profile $profile \
      --load-balancer-arn $alb_arns \
      --query 'TargetGroups[].[TargetGroupArn]' \
      --output text` || return $?

      #Target Groupsがなければalbを出力
      if [ -z "$target_groups" ]; then
        echo "$alb_arns ターゲットグループ無し"
      fi

      #Target Groupsに紐づいたホストの状態を一覧表示
      for target_group_arn in ${target_groups[@]}
      do
        local target_group_name=`sed 's%^.*:.*/\(.*\)\.*/.*%\1%' <<< $target_group_arn`
        
        #可視性のため、EC2のNameタグを取得する
        local instance=`aws elbv2 describe-target-health \
          --target-group-arn $target_group_arn \
          --profile $profile \
          --query 'TargetHealthDescriptions[].[Target.Id,TargetHealth.State]' \
          --output text`

          #$instanceに何もなければ$alb_arnsを出力
          if [ -z "$instance" ]; then
            echo "$alb_arns インスタンス無し"
          fi
      done
}

#Profileエラー用
if [ $# -ne 1 ]; then
  echo "Missing Argument"
  echo 'Usage: Profile'
  exit 1
fi

alb_show_health $@

